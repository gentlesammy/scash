<?php

namespace Database\Factories;

use App\Models\Evidence;
use App\Models\Report;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Evidence>
 */
class EvidenceFactory extends Factory
{
    protected $model = Evidence::class;

    public function definition(): array
    {
        $reportId = Report::inRandomOrder()->first()?->id ?? Report::factory();
        $uuid = $this->faker->uuid();
        
        return [
            'report_id' => $reportId,
            'type' => $this->faker->randomElement(['receipt', 'screenshot', 'chat_log']),
            
            // Simulates private file path on S3
            'file_path' => "evidence/original/{$uuid}.jpg",
            
            // Simulates public/redacted version
            'redacted_file_path' => $this->faker->boolean(80) 
                ? "evidence/redacted/{$uuid}_redacted.jpg" 
                : null,
        ];
    }
}
