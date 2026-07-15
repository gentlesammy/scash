<?php

namespace Database\Factories;

use App\Models\Report;
use App\Models\ScamCategory;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Report>
 */
class ReportFactory extends Factory
{
    protected $model = Report::class;

    private const NIGERIAN_BANKS = [
        'GTBank', 'Access Bank', 'Zenith Bank', 'United Bank for Africa (UBA)',
        'First Bank of Nigeria', 'Union Bank', 'Fidelity Bank', 'Wema Bank',
        'Kuda Bank', 'OPay', 'Palmpay', 'Moniepoint'
    ];

    public function definition(): array
    {
        // Randomly assign which identifiers are populated (at least one)
        $hasBank = $this->faker->boolean(70); // 70% chance of bank details
        $hasPhone = $this->faker->boolean(60); // 60% chance of phone
        $hasEmail = $this->faker->boolean(40); // 40% chance of email

        // Fallback: if all false, force phone
        if (!$hasBank && !$hasPhone && !$hasEmail) {
            $hasPhone = true;
        }

        return [
            'user_id' => User::inRandomOrder()->first()?->id ?? User::factory(),
            'scam_category_id' => ScamCategory::inRandomOrder()->first()?->id ?? ScamCategory::factory(),
            
            'bank_account_number' => $hasBank ? $this->faker->numerify('##########') : null,
            'bank_name' => $hasBank ? $this->faker->randomElement(self::NIGERIAN_BANKS) : null,
            
            // Nigerian format phone number
            'phone_number' => $hasPhone ? $this->faker->numerify('080########') : null,
            'email_address' => $hasEmail ? $this->faker->unique()->safeEmail() : null,
            
            'narrative' => $this->faker->paragraph(3),
            'stage' => $this->faker->randomElement(['stage_1', 'stage_2']),
            
            'weighted_credibility' => $this->faker->randomFloat(4, 0, 100),
            'ranking_score' => $this->faker->randomFloat(6, 0, 100),
        ];
    }
}
