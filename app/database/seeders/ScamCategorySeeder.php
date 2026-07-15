<?php

namespace Database\Seeders;

use App\Models\ScamCategory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ScamCategorySeeder extends Seeder
{
    private const CATEGORIES = [
        [
            'name' => 'Never Delivered',
            'description' => 'Vendor cut communication/ghosted after receiving payment and did not deliver items.'
        ],
        [
            'name' => 'Delivered Defective/Counterfeit',
            'description' => 'Vendor delivered goods that were significantly damaged, defective, or fake copies of the advertised product.'
        ],
        [
            'name' => 'Impersonation',
            'description' => 'Scammer pretended to be a representative of a reputable brand, bank, or public figure.'
        ],
        [
            'name' => 'Refund Refusal',
            'description' => 'Vendor refused to honor reasonable return policies or issue refunds for missing/wrong items.'
        ],
        [
            'name' => 'Fake Investment',
            'description' => 'Schemes promising unrealistic returns on capital or cryptocurrency in a short timeframe.'
        ],
        [
            'name' => 'Phishing Link',
            'description' => 'Sender shared malicious URLs designed to steal banking credentials, passwords, or personal identity details.'
        ]
    ];

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        foreach (self::CATEGORIES as $cat) {
            ScamCategory::firstOrCreate(
                ['slug' => Str::slug($cat['name'])],
                [
                    'name' => $cat['name'],
                    'description' => $cat['description']
                ]
            );
        }
    }
}
