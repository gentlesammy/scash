<?php

namespace Database\Factories;

use App\Models\VerifiedVendor;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<VerifiedVendor>
 */
class VerifiedVendorFactory extends Factory
{
    protected $model = VerifiedVendor::class;

    private const NIGERIAN_BANKS = [
        'GTBank', 'Access Bank', 'Zenith Bank', 'United Bank for Africa (UBA)',
        'First Bank of Nigeria', 'Kuda Bank', 'OPay'
    ];

    public function definition(): array
    {
        $businessName = $this->faker->company();
        return [
            'business_name' => $businessName,
            'bank_account_number' => $this->faker->unique()->numerify('##########'),
            'bank_name' => $this->faker->randomElement(self::NIGERIAN_BANKS),
            'phone_number' => $this->faker->unique()->numerify('080########'),
            'email_address' => $this->faker->unique()->safeEmail(),
            'verification_badge_url' => 'badges/verified_badge.svg',
            'notes' => 'Whitelisted safe merchant with proven history.',
        ];
    }
}
