<?php

namespace App\Observers;

use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Support\Str;

class UserObserver
{
    /**
     * Handle the User "creating" event.
     */
    public function creating(User $user): void
    {
        if (empty($user->pseudonym)) {
            $user->pseudonym = $this->generateUniquePseudonym();
        }
    }

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

    /**
     * Generate a unique pseudonym.
     */
    private function generateUniquePseudonym(): string
    {
        $adjectives = ['Brave', 'Calm', 'Clever', 'Fierce', 'Gentle', 'Happy', 'Jolly', 'Kind', 'Lively', 'Proud', 'Silly', 'Smart', 'Swift', 'Wise', 'Zealous', 'Keen', 'Bright', 'Noble', 'Valiant'];
        $nouns = ['Eagle', 'Tiger', 'Lion', 'Bear', 'Wolf', 'Fox', 'Hawk', 'Owl', 'Shark', 'Whale', 'Dolphin', 'Panther', 'Falcon', 'Sentinel', 'Watcher', 'Guard', 'Shield', 'Beacon', 'Patrol'];

        do {
            $adjective = $adjectives[array_rand($adjectives)];
            $noun = $nouns[array_rand($nouns)];
            $number = rand(100, 9999);
            
            $pseudonym = "{$adjective}{$noun}_{$number}";
        } while (User::where('pseudonym', $pseudonym)->exists());

        return $pseudonym;
    }
}
