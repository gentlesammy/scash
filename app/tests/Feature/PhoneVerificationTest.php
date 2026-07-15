<?php

namespace Tests\Feature;

use App\Livewire\Auth\VerifyPhone;
use App\Models\OtpVerification;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class PhoneVerificationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Seed roles so user role (ID 4) works
        $this->seed(RoleSeeder::class);
    }

    /**
     * Test user cannot see dashboard if phone is unverified.
     */
    public function test_user_is_redirected_to_verify_phone_if_unverified(): void
    {
        $user = User::factory()->create([
            'phone_verified_at' => null,
        ]);

        $this->actingAs($user)
            ->get('/dashboard')
            ->assertRedirect(route('verification.phone'));
    }

    /**
     * Test phone verification success.
     */
    public function test_phone_verification_success(): void
    {
        $user = User::factory()->create([
            'phone' => '08031234567',
            'phone_verified_at' => null,
        ]);

        // Generate an active OTP code for this user's phone
        $otp = OtpVerification::create([
            'phone' => $user->phone,
            'code' => '123456',
            'expires_at' => now()->addMinutes(10),
            'ip_address' => '127.0.0.1',
            'channel' => 'sms',
            'is_verified' => false,
        ]);

        $this->actingAs($user);

        // Submit the correct OTP code
        Livewire::test(VerifyPhone::class)
            ->set('otp', '123456')
            ->call('verify')
            ->assertHasNoErrors(['otp'])
            ->assertRedirect(route('dashboard'));

        // Assert that the user's phone_verified_at field was updated
        $this->assertNotNull($user->fresh()->phone_verified_at);

        // Assert the OTP was marked verified
        $this->assertTrue($otp->fresh()->is_verified);
    }

    /**
     * Test phone verification with incorrect OTP.
     */
    public function test_phone_verification_fails_with_invalid_otp(): void
    {
        $user = User::factory()->create([
            'phone' => '08031234567',
            'phone_verified_at' => null,
        ]);

        // Generate an OTP code
        OtpVerification::create([
            'phone' => $user->phone,
            'code' => '123456',
            'expires_at' => now()->addMinutes(10),
            'ip_address' => '127.0.0.1',
            'channel' => 'sms',
            'is_verified' => false,
        ]);

        $this->actingAs($user);

        // Submit incorrect OTP code
        Livewire::test(VerifyPhone::class)
            ->set('otp', '999999')
            ->call('verify')
            ->assertHasErrors(['otp']);

        // Assert user is still unverified
        $this->assertNull($user->fresh()->phone_verified_at);
    }
}
