<?php

namespace Tests\Feature;

use App\Livewire\SearchVendor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class SearchVendorTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test validation for bank account numbers (must be exactly 10 digits).
     */
    public function test_bank_account_validation(): void
    {
        // Valid 10 digit bank accounts
        Livewire::test(SearchVendor::class)
            ->set('type', 'bank')
            ->set('query', '0123456789')
            ->call('search')
            ->assertHasNoErrors(['query']);

        // Check spaces/dashes sanitation
        Livewire::test(SearchVendor::class)
            ->set('type', 'bank')
            ->set('query', '012 345-6789')
            ->call('search')
            ->assertHasNoErrors(['query']);

        // Invalid bank accounts (too short, letters, or too long)
        Livewire::test(SearchVendor::class)
            ->set('type', 'bank')
            ->set('query', '123456789')
            ->call('search')
            ->assertHasErrors(['query' => 'regex']);

        Livewire::test(SearchVendor::class)
            ->set('type', 'bank')
            ->set('query', '0123456789a')
            ->call('search')
            ->assertHasErrors(['query' => 'regex']);
    }

    /**
     * Test validation for phone numbers (must be exactly 11 digits starting with 0, no international prefixes).
     */
    public function test_phone_number_validation(): void
    {
        // Valid 11 digit phone numbers starting with 0
        Livewire::test(SearchVendor::class)
            ->set('type', 'phone')
            ->set('query', '08031234567')
            ->call('search')
            ->assertHasNoErrors(['query']);

        // Check spaces/dashes sanitation
        Livewire::test(SearchVendor::class)
            ->set('type', 'phone')
            ->set('query', '080-3123-4567')
            ->call('search')
            ->assertHasNoErrors(['query']);

        // Invalid phone numbers (starting with international prefix or +234)
        Livewire::test(SearchVendor::class)
            ->set('type', 'phone')
            ->set('query', '+2348031234567')
            ->call('search')
            ->assertHasErrors(['query' => 'regex']);

        Livewire::test(SearchVendor::class)
            ->set('type', 'phone')
            ->set('query', '2348031234567')
            ->call('search')
            ->assertHasErrors(['query' => 'regex']);

        Livewire::test(SearchVendor::class)
            ->set('type', 'phone')
            ->set('query', '0803123456') // 10 digits
            ->call('search')
            ->assertHasErrors(['query' => 'regex']);
    }

    /**
     * Test validation for email addresses.
     */
    public function test_email_validation(): void
    {
        // Valid email
        Livewire::test(SearchVendor::class)
            ->set('type', 'email')
            ->set('query', 'test@scash.com.ng')
            ->call('search')
            ->assertHasNoErrors(['query']);

        // Invalid email
        Livewire::test(SearchVendor::class)
            ->set('type', 'email')
            ->set('query', 'testscash.com.ng')
            ->call('search')
            ->assertHasErrors(['query' => 'email']);
    }

    /**
     * Test that search results and validation states are reset.
     */
    public function test_search_results_reset_on_type_change_or_new_invalid_search(): void
    {
        // Start search
        $component = Livewire::test(SearchVendor::class)
            ->set('type', 'bank')
            ->set('query', '0123456789')
            ->set('recaptchaToken', 'mock-token')
            ->call('search')
            ->assertSet('searched', true);

        // Change select type
        $component->set('type', 'phone')
            ->assertSet('searched', false)
            ->assertSet('results', null);

        // Run search again with correct phone, checking searched is true
        $component->set('query', '08031234567')
            ->call('search')
            ->assertSet('searched', true);

        // Run search with invalid query (fails validation), checking results and searched are reset
        $component->set('query', 'invalid-phone')
            ->call('search')
            ->assertHasErrors(['query'])
            ->assertSet('searched', false)
            ->assertSet('results', null);
    }
}
