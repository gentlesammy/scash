<?php

namespace Database\Seeders;

use App\Models\Evidence;
use App\Models\Rating;
use App\Models\Report;
use App\Models\ScamCategory;
use App\Models\User;
use App\Models\VerifiedVendor;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->command->info('Starting SCASH Seeders...');

        // 1. Seed Roles and Superadmin
        $this->call([
            RoleSeeder::class,
            SuperadminSeeder::class,
            ScamCategorySeeder::class,
        ]);

        $this->command->info('Roles, Admins, and Scam Categories seeded.');

        // 2. Generate regular users, moderators, and additional admins
        $this->command->info('Seeding User Accounts...');
        $users = User::factory()->count(40)->create();
        $mods = User::factory()->moderator()->count(5)->create();
        $admins = User::factory()->admin()->count(2)->create();
        
        $allUsers = $users->concat($mods)->concat($admins);

        // 3. Generate 100 reports
        $this->command->info('Seeding 100 Reports, Evidences and Ratings...');
        
        // Let's create reports one by one to attach evidences & ratings safely
        $categories = ScamCategory::all();

        for ($i = 0; $i < 100; $i++) {
            // Select a reporter (must be a regular user or mod)
            $reporter = $users->random();
            $category = $categories->random();

            $report = Report::factory()->create([
                'user_id' => $reporter->id,
                'scam_category_id' => $category->id,
            ]);

            // Create 1 to 3 evidences for this report
            $evidenceCount = random_int(1, 3);
            Evidence::factory()->count($evidenceCount)->create([
                'report_id' => $report->id,
            ]);

            // Create 1 to 6 credibility ratings from OTHER users (not the reporter)
            $potentialRaters = $allUsers->where('id', '!=', $reporter->id)->shuffle();
            $ratingCount = random_int(1, 6);
            
            $weightedCredibilitySum = 0;
            
            for ($r = 0; $r < $ratingCount; $r++) {
                if ($potentialRaters->isEmpty()) {
                    break;
                }
                $rater = $potentialRaters->pop();
                $score = random_int(1, 10);

                Rating::create([
                    'user_id' => $rater->id,
                    'report_id' => $report->id,
                    'score' => $score,
                ]);

                // Calculate weighted contribution
                $weightedCredibilitySum += ($score * $rater->credibility_rank);
            }

            // Update computed fields on report
            // For seeding: ranking score can be calculated via simplified gravity
            $hoursAgo = random_int(0, 168); // up to 7 days old
            $report->created_at = now()->subHours($hoursAgo);
            $report->weighted_credibility = $weightedCredibilitySum;
            
            // W / (T + 2)^G  where G = 1.8
            $gravity = 1.8;
            $report->ranking_score = $weightedCredibilitySum / pow($hoursAgo + 2, $gravity);
            $report->save();
        }

        $this->command->info('100 Reports seeded successfully.');

        // 4. Seed Verified Whitelisted Vendors (Stage 3 mockup)
        $this->command->info('Seeding Whitelisted Safe Vendors...');
        VerifiedVendor::factory()->count(10)->create();

        $this->command->info('All seeders completed successfully!');
    }
}
