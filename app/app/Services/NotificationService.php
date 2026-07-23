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
