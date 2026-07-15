<?php

namespace Tests\Feature;

use App\Livewire\Auth\Login;
use App\Livewire\Auth\Register;
use App\Livewire\SearchVendor;
use App\Livewire\RateEvidence;
use App\Services\RecaptchaService;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class RecaptchaVerificationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleSeeder::class);
    }

    /**
     * Test that forms fail validation if reCAPTCHA verification fails.
     */
    public function test_forms_fail_when_recaptcha_verification_fails(): void
    {
        // Mock RecaptchaService to return false for our token
        $this->mock(RecaptchaService::class, function ($mock) {
            $mock->shouldReceive('verify')->andReturn(false);
        });

        // 1. Search Form
        Livewire::test(SearchVendor::class)
            ->set('type', 'bank')
            ->set('query', '0123456789')
            ->set('recaptchaToken', 'bad-token')
            ->call('search')
            ->assertHasErrors(['recaptchaToken']);

        // 2. Login Form
        Livewire::test(Login::class)
            ->set('email', 'test@scash.com.ng')
            ->set('password', 'password123')
            ->set('recaptchaToken', 'bad-token')
            ->call('login')
            ->assertHasErrors(['recaptchaToken']);

        // 3. Register Form
        Livewire::test(Register::class)
            ->set('phone', '08031234567')
            ->set('email', 'new@scash.com.ng')
            ->set('password', 'password123')
            ->set('password_confirmation', 'password123')
            ->set('recaptchaToken', 'bad-token')
            ->call('register')
            ->assertHasErrors(['recaptchaToken']);
    }

    /**
     * Test that forms pass validation if reCAPTCHA verification succeeds.
     */
    public function test_forms_pass_when_recaptcha_verification_succeeds(): void
    {
        // Mock RecaptchaService to return true for our token
        $this->mock(RecaptchaService::class, function ($mock) {
            $mock->shouldReceive('verify')->andReturn(true);
        });

        // 1. Search Form
        Livewire::test(SearchVendor::class)
            ->set('type', 'bank')
            ->set('query', '0123456789')
            ->set('recaptchaToken', 'good-token')
            ->call('search')
            ->assertHasNoErrors(['recaptchaToken']);
    }

    /**
     * Test submit rating reCAPTCHA validation (authenticated flow).
     */
    public function test_auth_forms_validate_recaptcha(): void
    {
        // Seed categories
        \App\Models\ScamCategory::create(['name' => 'Online Retail Scams', 'slug' => 'retail']);

        $author = \App\Models\User::factory()->create();
        $rater = \App\Models\User::factory()->create();
        $this->actingAs($rater);

        // Create a report to rate (authored by $author)
        $report = \App\Models\Report::create([
            'user_id' => $author->id,
            'scam_category_id' => 1,
            'bank_account_number' => '0123456789',
            'stage' => 'stage_1',
            'weighted_credibility' => 0.00,
            'ranking_score' => 0.00,
        ]);

        // Mock RecaptchaService to return false
        $this->mock(RecaptchaService::class, function ($mock) {
            $mock->shouldReceive('verify')->andReturn(false);
        });

        // Rate Evidence Form
        Livewire::test(RateEvidence::class, ['reportId' => $report->id])
            ->set('score', 8)
            ->set('recaptchaToken', 'bad-token')
            ->call('submitRating')
            ->assertHasErrors(['recaptchaToken']);
    }
}
