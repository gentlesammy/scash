# In-App Notification System — Architecture Plan

> **Architect:** Senior Review  
> **Date:** July 22, 2026  
> **Scope:** Database-backed, in-app notification system for SCASH  
> **Delivery channel:** In-app only (no email/SMS/push — those are future phases)

---

## Background & Motivation

SCASH currently has **zero feedback mechanisms** for users after they take an action. A user submits a report and never knows when it gets verified or marked fake. A user earns or loses Trust Points but only sees the ledger on their dashboard — they have to manually check. Moderators have no way to notify users of bans or policy actions.

This creates a dead-end user experience: the platform rewards and penalizes users through the Trust Point system, but users are **never proactively informed** about what happened or why.

### What this plan delivers

A lightweight, database-backed notification system that:
- Tells users when something meaningful happens to them
- Shows an unread count badge in the navbar (bell icon)
- Provides a dropdown preview + full notifications page
- Integrates into 7 existing code paths with zero disruption to current flows

---

## Notification Event Catalog

These are the events that generate notifications, mapped from the existing codebase:

| # | Event | Trigger Location | Recipient | Icon | Priority |
|---|-------|-------------------|-----------|------|----------|
| 1 | **Report verified** | `Admin\Reports::verifyReport()` | Report author | ✅ | High |
| 2 | **Report marked fake** | `Admin\Reports::markFakeReport()` | Report author | ❌ | High |
| 3 | **Report escalated** | `Admin\Reports::escalateReport()` | Report author | ⚠️ | Medium |
| 4 | **Trust Points awarded** | `TrustService::awardPoints()` | Point recipient | 🏆 | Medium |
| 5 | **Trust Points deducted** | `TrustService::deductPoints()` | Point recipient | 📉 | High |
| 6 | **Credibility rank changed** | `TrustService::recalculateRank()` | Ranked user | 🎖️ | Medium |
| 7 | **Account banned** | `Admin\Users::banUser()` | Banned user | 🚫 | Critical |
| 8 | **Account unbanned** | `Admin\Users::unbanUser()` | Unbanned user | 🔓 | High |
| 9 | **Report received new rating** | `RateEvidence::submitRating()` | Report author | ⭐ | Low |
| 10 | **Report hit 10 ratings milestone** | `RateEvidence::submitRating()` | Report author | 🎯 | Medium |
| 11 | **Welcome notification** | `UserObserver::creating()` | New user | 👋 | Low |

> **Note on frequency:** Events 4, 5, and 6 are **high-frequency** in moderation workflows (a single "mark fake" action can trigger 10+ TP adjustments). The plan addresses this with batching in the service layer to avoid notification spam.

---

## Proposed Changes

---

### 1. Database — Notifications Table

#### [NEW] `database/migrations/0001_01_01_000014_create_notifications_table.php`

```php
Schema::create('notifications', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained()->onDelete('cascade');
    $table->string('type', 64);           // Machine key: 'report_verified', 'points_awarded', etc.
    $table->string('title');               // Human-readable headline
    $table->text('body')->nullable();      // Optional detail text
    $table->string('icon', 32)->default('bi-bell-fill');  // Bootstrap Icon class
    $table->string('action_url')->nullable();  // Clickable link (e.g. /report/42)
    $table->unsignedBigInteger('related_report_id')->nullable();
    $table->foreign('related_report_id')->references('id')->on('reports')->onDelete('set null');
    $table->timestamp('read_at')->nullable();  // null = unread
    $table->timestamps();

    // Performance indexes
    $table->index(['user_id', 'read_at']);           // Unread count query
    $table->index(['user_id', 'created_at']);         // Paginated list query
});
```

**Design rationale:**
- `type` is a machine key for filtering/grouping; `title` and `body` are pre-rendered human text — no runtime template resolution needed
- `related_report_id` uses `SET NULL` on delete (same pattern as `trust_point_logs`) so notification history survives report cleanup
- `read_at` nullable timestamp (not boolean) lets us show "read 3 hours ago" later if needed
- Composite indexes cover the two hot queries: unread badge count and paginated list

---

### 2. Model — Notification

#### [NEW] `app/Models/Notification.php`

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Notification extends Model
{
    protected $fillable = [
        'user_id', 'type', 'title', 'body', 'icon',
        'action_url', 'related_report_id', 'read_at',
    ];

    protected function casts(): array
    {
        return ['read_at' => 'datetime'];
    }

    /* ─── Relationships ─── */

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function report(): BelongsTo
    {
        return $this->belongsTo(Report::class, 'related_report_id');
    }

    /* ─── Scopes ─── */

    public function scopeUnread($query)
    {
        return $query->whereNull('read_at');
    }

    public function scopeRecent($query)
    {
        return $query->orderBy('created_at', 'desc');
    }

    /* ─── Actions ─── */

    public function markAsRead(): void
    {
        if (!$this->read_at) {
            $this->update(['read_at' => now()]);
        }
    }
}
```

Also add the inverse relationship on **`app/Models/User.php`** — add these two methods:

```php
use Illuminate\Database\Eloquent\Relations\HasMany;

public function notifications(): HasMany
{
    return $this->hasMany(Notification::class);
}

public function unreadNotificationCount(): int
{
    // Uses the composite index on (user_id, read_at)
    return $this->notifications()->unread()->count();
}
```

---

### 3. Service — NotificationService

#### [NEW] `app/Services/NotificationService.php`

A centralized service that all producers call. This prevents notification creation logic from being scattered across 7+ files.

```php
<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\User;

class NotificationService
{
    /**
     * Type configuration map.
     * Defines the icon and URL pattern for each notification type.
     */
    private const TYPE_CONFIG = [
        'report_verified'       => ['icon' => 'bi-check-circle-fill',    'url' => '/report/{report_id}'],
        'report_marked_fake'    => ['icon' => 'bi-x-circle-fill',        'url' => '/report/{report_id}'],
        'report_escalated'      => ['icon' => 'bi-exclamation-triangle-fill', 'url' => '/report/{report_id}'],
        'points_awarded'        => ['icon' => 'bi-trophy-fill',          'url' => '/dashboard'],
        'points_deducted'       => ['icon' => 'bi-graph-down-arrow',     'url' => '/dashboard'],
        'rank_changed'          => ['icon' => 'bi-award-fill',           'url' => '/dashboard'],
        'account_banned'        => ['icon' => 'bi-slash-circle-fill',    'url' => null],
        'account_unbanned'      => ['icon' => 'bi-unlock-fill',          'url' => null],
        'report_rated'          => ['icon' => 'bi-star-fill',            'url' => '/report/{report_id}'],
        'report_milestone'      => ['icon' => 'bi-bullseye',             'url' => '/report/{report_id}'],
        'welcome'               => ['icon' => 'bi-hand-wave',            'url' => '/dashboard'],
    ];

    /**
     * Send a notification to a user.
     *
     * @param User    $user     The recipient user
     * @param string  $type     Machine key from TYPE_CONFIG (e.g. 'report_verified')
     * @param string  $title    Human-readable headline shown in the notification
     * @param ?string $body     Optional detail text
     * @param ?int    $reportId Optional related report ID for deep linking
     */
    public function send(User $user, string $type, string $title, ?string $body = null, ?int $reportId = null): void
    {
        $config = self::TYPE_CONFIG[$type] ?? ['icon' => 'bi-bell-fill', 'url' => null];
        $url = $config['url'] ? str_replace('{report_id}', (string) $reportId, $config['url']) : null;

        Notification::create([
            'user_id'            => $user->id,
            'type'               => $type,
            'title'              => $title,
            'icon'               => $config['icon'],
            'body'               => $body,
            'action_url'         => $url,
            'related_report_id'  => $reportId,
        ]);
    }
}
```

**Spam prevention note:** When `Admin\Reports::markFakeReport()` fires, it triggers `deductPoints` on the author, `deductPoints` on each false endorser, and `awardPoints` on each skeptic. Each of those calls `NotificationService::send()`. For a report with 20 raters, this could create **22 notifications** — but each goes to a **different user** (each rater). The author receives exactly 2 notifications: "report marked fake" and "points deducted". This is acceptable without batching in v1. If noise appears later, consolidate TP notifications with a `created_at` window grouping.

---

### 4. Integration Points — Producing Notifications

These are surgical additions — typically 1–3 lines appended after existing `session()->flash()` calls.

---

#### [MODIFY] `app/Livewire/Admin/Reports.php`

Three methods gain notification calls:

**`verifyReport()`** — add after the `session()->flash()` call (currently line 54):
```php
$notificationService = app(NotificationService::class);
if ($report->user) {
    $notificationService->send(
        $report->user,
        'report_verified',
        'Your report has been verified!',
        "Report #{$reportId} was reviewed and confirmed by a moderator. Aligned raters have been rewarded.",
        $reportId
    );
}
```

**`markFakeReport()`** — add after `session()->flash()` (currently line 85):
```php
$notificationService = app(NotificationService::class);
if ($report->user) {
    $notificationService->send(
        $report->user,
        'report_marked_fake',
        'Your report was marked as fake',
        "Report #{$reportId} has been reviewed and determined to be fabricated. A Trust Point penalty has been applied.",
        $reportId
    );
}
```

**`escalateReport()`** — add after `session()->flash()` (currently line 98):
```php
$notificationService = app(NotificationService::class);
$report = Report::with('user')->findOrFail($reportId); // note: re-fetch with user relation
if ($report->user) {
    $notificationService->send(
        $report->user,
        'report_escalated',
        'Your report has been escalated',
        "Report #{$reportId} has been flagged for additional admin review.",
        $reportId
    );
}
```

**Important:** Add `use App\Services\NotificationService;` to the imports at the top of this file.

---

#### [MODIFY] `app/Livewire/Admin/Users.php`

**`banUser()`** — add after `session()->flash()` (currently line 61):
```php
app(NotificationService::class)->send(
    $user, 'account_banned',
    'Your account has been suspended',
    'Your account has been permanently banned by an administrator. If you believe this is an error, contact support.'
);
```

**`unbanUser()`** — add after `session()->flash()` (currently line 78):
```php
app(NotificationService::class)->send(
    $user, 'account_unbanned',
    'Your account has been restored',
    'Your account ban has been lifted. Welcome back to the SCASH community.'
);
```

**Important:** Add `use App\Services\NotificationService;` to the imports.

---

#### [MODIFY] `app/Services/TrustService.php`

**`awardPoints()`** — add after `$this->recalculateRank($user)` (currently line 44):
```php
app(NotificationService::class)->send(
    $user, 'points_awarded',
    "+{$points} Trust Points earned",
    ucwords(str_replace('_', ' ', $reason)),
    $reportId
);
```

**`deductPoints()`** — add after `$this->recalculateRank($user)` (currently line 69):
```php
app(NotificationService::class)->send(
    $user, 'points_deducted',
    "-{$deduction} Trust Points deducted",
    ucwords(str_replace('_', ' ', $reason)),
    $reportId
);
```

**`recalculateRank()`** — modify the existing rank-changed `if` block (currently line 90-92) to also send a notification:
```php
if ($user->credibility_rank !== $targetRank) {
    $oldRank = $user->credibility_rank;
    $user->update(['credibility_rank' => $targetRank]);

    $direction = $targetRank > $oldRank ? 'promoted' : 'demoted';
    app(NotificationService::class)->send(
        $user, 'rank_changed',
        "Credibility rank {$direction} to Rank {$targetRank}",
        'Your credibility rank has changed based on your Trust Point balance.'
    );
}
```

**Important:** Add `use App\Services\NotificationService;` to the imports. Note: this is a self-referencing import from the same namespace, but since it's a different class file it still needs the use statement (or use `app()` container resolution as shown above).

---

#### [MODIFY] `app/Livewire/RateEvidence.php`

**`submitRating()`** — after the rating is saved (currently line 94) and the report is fetched, add notification to report author:

```php
// The report is already fetched at line 97 as $report = Report::findOrFail(...)
// Ensure user relationship is loaded:
$report->loadMissing('user');

if ($report->user && $report->user->id !== $user->id) {
    app(NotificationService::class)->send(
        $report->user, 'report_rated',
        'Your report received a new rating',
        "A community member rated Report #{$this->reportId} with a credibility score of {$this->score}/10.",
        $this->reportId
    );
}
```

For the **10-rating milestone** (currently line 116), add alongside the existing `awardPoints` call:
```php
if ($ratings->count() === 10 && $author) {
    // Existing: $trustService->awardPoints(...)
    app(NotificationService::class)->send(
        $author, 'report_milestone',
        'Milestone: 10 community ratings received!',
        "Report #{$report->id} has reached 10 ratings. +5 Trust Points have been awarded.",
        $report->id
    );
}
```

**Important:** Add `use App\Services\NotificationService;` to the imports.

---

#### [MODIFY] `app/Observers/UserObserver.php`

Add a new `created` hook (the existing `creating` hook assigns the pseudonym and must stay):

```php
use App\Services\NotificationService;

/**
 * Handle the User "created" event (after the row exists in DB).
 */
public function created(User $user): void
{
    app(NotificationService::class)->send(
        $user, 'welcome',
        'Welcome to SCASH!',
        'Your account has been created. Start by verifying a vendor or reporting a scam to earn Trust Points.'
    );
}
```

**Why `created` and not `creating`:** The notification requires the user's `id` to exist in the database (foreign key constraint). The `creating` event fires before the INSERT, so the `id` doesn't exist yet.

---

### 5. Consuming Notifications — UI Components

---

#### [NEW] `app/Livewire/NotificationBell.php`

A small Livewire component rendered **inside the navbar layout**. It:
- Queries `auth()->user()->notifications()->unread()->count()` on mount (uses the composite index)
- Shows a red badge with the count
- Renders a dropdown with the 5 most recent notifications
- Has a `markAsRead($id)` method and a `markAllAsRead()` method
- Polls every 30 seconds for new notifications (`wire:poll.30s`)

```php
<?php

namespace App\Livewire;

use App\Models\Notification;
use Livewire\Component;

class NotificationBell extends Component
{
    public int $unreadCount = 0;

    public function mount(): void
    {
        $this->loadCount();
    }

    public function loadCount(): void
    {
        $this->unreadCount = auth()->user()?->notifications()->unread()->count() ?? 0;
    }

    public function markAsRead(int $id): void
    {
        $notification = Notification::where('user_id', auth()->id())->findOrFail($id);
        $notification->markAsRead();
        $this->loadCount();
    }

    public function markAllAsRead(): void
    {
        auth()->user()->notifications()->unread()->update(['read_at' => now()]);
        $this->unreadCount = 0;
    }

    public function render()
    {
        $recent = auth()->user()
            ->notifications()
            ->recent()
            ->limit(5)
            ->get();

        return view('livewire.notification-bell', ['notifications' => $recent]);
    }
}
```

---

#### [NEW] `resources/views/livewire/notification-bell.blade.php`

A Bootstrap 5 dropdown inside the navbar. Key design specs:
- Bell icon (`bi-bell-fill`) with a red badge counter (hidden when `$unreadCount === 0`)
- Dropdown panel with the 5 latest notifications, each showing: icon + title + relative time (`created_at->diffForHumans()`)
- Unread items have a subtle left-border accent using `var(--emerald)` color
- "Mark all as read" button at the top of the dropdown
- "View all notifications" link at the bottom linking to `/dashboard/notifications`
- Add `wire:poll.30s` to the root div for auto-refresh
- Style consistent with the existing navbar dropdown (see `app.blade.php` lines 63-72 for the existing user dropdown pattern)

---

#### [NEW] `app/Livewire/NotificationsPage.php`

Full-page notification center accessible at `/dashboard/notifications`:

```php
<?php

namespace App\Livewire;

use App\Models\Notification;
use Livewire\Component;
use Livewire\WithPagination;

class NotificationsPage extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public string $filter = 'all'; // 'all' or 'unread'

    public function updatingFilter(): void
    {
        $this->resetPage();
    }

    public function markAsRead(int $id): mixed
    {
        $notification = Notification::where('user_id', auth()->id())->findOrFail($id);
        $notification->markAsRead();

        if ($notification->action_url) {
            return redirect($notification->action_url);
        }

        return null;
    }

    public function markAllAsRead(): void
    {
        auth()->user()->notifications()->unread()->update(['read_at' => now()]);
    }

    public function render()
    {
        $query = auth()->user()->notifications()->recent();

        if ($this->filter === 'unread') {
            $query->unread();
        }

        return view('livewire.notifications-page', [
            'notifications' => $query->paginate(10),
            'unreadCount' => auth()->user()->notifications()->unread()->count(),
        ])->layout('layouts.app');
    }
}
```

---

#### [NEW] `resources/views/livewire/notifications-page.blade.php`

Styled consistently with the existing `user-dashboard.blade.php` pattern. Key design specs:
- Same `card border-0 shadow-sm rounded-3 p-4 bg-white` container
- Header with "Notifications" title and "Mark all as read" button
- Filter tabs: All / Unread (same pill button style as admin report status filters)
- Each notification rendered as a clickable list item with: icon (using the `icon` field Bootstrap Icon class), title, body, relative timestamp (`diffForHumans()`), and unread indicator (left border accent)
- Empty state with `bi-bell-slash` icon and "No notifications" message
- Pagination at the bottom

---

### 6. Layout & Route Integration

#### [MODIFY] `resources/views/layouts/app.blade.php`

In the navbar, between the "Moderator Panel" link and the user dropdown (around line 59), insert:

```blade
@auth
    <li class="nav-item">
        @livewire('notification-bell')
    </li>
@endauth
```

This should go inside the `<ul class="navbar-nav ...">` element, just before the user dropdown `<li>`.

---

#### [MODIFY] `routes/web.php`

Add near the existing dashboard route (around line 67-69):

```php
Route::get('/dashboard/notifications', \App\Livewire\NotificationsPage::class)
    ->name('dashboard.notifications')
    ->middleware(['auth', 'not.banned', 'phone.verified', 'email.verified']);
```

---

### 7. Dashboard Tab Integration (Optional Enhancement)

#### [MODIFY] `resources/views/livewire/user-dashboard.blade.php`

Add a **"Notifications" link** inside the dashboard sidebar card or next to the tab navigation, showing the unread count inline. This is a convenience link — the primary access point is the navbar bell.

---

## File Summary

| Action | File | Purpose |
|--------|------|---------|
| **NEW** | `database/migrations/0001_01_01_000014_create_notifications_table.php` | Schema + indexes |
| **NEW** | `app/Models/Notification.php` | Eloquent model with scopes |
| **NEW** | `app/Services/NotificationService.php` | Centralized notification factory |
| **NEW** | `app/Livewire/NotificationBell.php` | Navbar bell + dropdown |
| **NEW** | `resources/views/livewire/notification-bell.blade.php` | Bell dropdown UI |
| **NEW** | `app/Livewire/NotificationsPage.php` | Full notifications page |
| **NEW** | `resources/views/livewire/notifications-page.blade.php` | Full page UI |
| **MODIFY** | `app/Models/User.php` | Add `notifications()` relationship + `unreadNotificationCount()` |
| **MODIFY** | `app/Livewire/Admin/Reports.php` | 3 notification calls (verify, fake, escalate) |
| **MODIFY** | `app/Livewire/Admin/Users.php` | 2 notification calls (ban, unban) |
| **MODIFY** | `app/Services/TrustService.php` | 3 notification calls (award, deduct, rank change) |
| **MODIFY** | `app/Livewire/RateEvidence.php` | 2 notification calls (new rating, milestone) |
| **MODIFY** | `app/Observers/UserObserver.php` | 1 welcome notification on `created` hook |
| **MODIFY** | `resources/views/layouts/app.blade.php` | Embed `@livewire('notification-bell')` in navbar |
| **MODIFY** | `routes/web.php` | Add `/dashboard/notifications` route |
| **MODIFY** | `resources/views/livewire/user-dashboard.blade.php` | Add notifications link/badge |

**Total: 7 new files, 9 modified files**

---

## Open Design Decisions

The implementing developer should decide (or ask the project owner) on these:

### 1. Rating notification frequency
When a report gets 50+ ratings, the author receives 50+ individual "your report received a rating" notifications. Options:
- **(A)** Send one per rating (simple, transparent — current plan default)
- **(B)** Batch into "Your report received 5 new ratings" grouped per hour
- **(C)** Only notify at milestones (10, 25, 50 ratings)

### 2. Banned user notifications
A banned user can't access the dashboard to read their "you've been banned" notification. Options:
- **(A)** Still create it — they'll see it if unbanned later (serves as historical record)
- **(B)** Skip it for banned users entirely
- **(C)** Show it on a special "your account is suspended" interstitial page

---

## Future Phases (Not In Scope)

These are explicitly **out of scope** for v1 but should be kept in mind during implementation to avoid blocking them:

- Email digest notifications (daily/weekly summary)
- Push notifications via the existing PWA service worker (`sw.js` is already registered)
- Notification preferences per user (opt-out of certain types)
- Real-time updates via Laravel Echo / WebSockets (replacing `wire:poll.30s`)

---

## Verification Plan

### Automated
```bash
php artisan migrate          # Verify the notifications table is created
php artisan tinker            # Manually create a notification and verify model relationships
```

### Manual Verification
1. **Register a new user** → check for welcome notification in the bell dropdown
2. **Submit a report** → as admin, verify the report → confirm author receives "report verified" notification
3. **Mark a report as fake** → confirm author receives "report marked fake" + "points deducted" notifications; confirm skeptical raters receive "points awarded" notification
4. **Ban a user** → confirm notification is created (visible after unban)
5. **Rate a report 10 times** → confirm author receives individual rating notifications + milestone notification
6. **Bell badge** → verify the red counter increments in real-time (within 30s polling interval)
7. **Mark as read** → click a notification → confirm it redirects to `action_url` and the badge count decrements
8. **Mark all as read** → confirm all notifications lose unread styling and badge goes to 0
9. **Full page** → visit `/dashboard/notifications` → confirm pagination, filter tabs, and empty state all work
