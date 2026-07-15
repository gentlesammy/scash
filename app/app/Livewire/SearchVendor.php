<?php

namespace App\Livewire;

use App\Services\SearchService;
use Livewire\Component;

class SearchVendor extends Component
{
    public string $query = '';
    public string $type = 'bank';
    public string $recaptchaToken = '';
    public ?array $results = null;
    public bool $searched = false;

    /**
     * Clear search results and validation errors when search type changes.
     */
    public function updatedType(): void
    {
        $this->results = null;
        $this->searched = false;
        $this->resetValidation();
    }

    /**
     * Executes vendor verification search with strict validation.
     */
    public function search(SearchService $searchService): void
    {
        // Clear previous results and reset validation upon new search execution
        $this->results = null;
        $this->searched = false;
        $this->resetValidation();

        // Clean query from user-friendly formats (whitespaces, dashes) before validating
        $this->query = trim($this->query);
        if ($this->type === 'bank' || $this->type === 'phone') {
            $this->query = preg_replace('/\s+|-/', '', $this->query);
        }

        // Define dynamic rules depending on search type
        $rules = [
            'type' => 'required|in:bank,phone,email',
            'recaptchaToken' => ['required', new \App\Rules\Recaptcha('search')],
        ];

        if ($this->type === 'bank') {
            $rules['query'] = ['required', 'regex:/^\d{10}$/'];
        } elseif ($this->type === 'phone') {
            $rules['query'] = ['required', 'regex:/^0\d{10}$/'];
        } else {
            $rules['query'] = ['required', 'email', 'max:100'];
        }

        // Define user-friendly error messages
        $messages = [
            'query.required' => 'Please enter a search query.',
            'query.regex' => $this->type === 'bank'
                ? 'The bank account number must be exactly 10 digits.'
                : 'The phone number must be exactly 11 digits starting with 0.',
            'query.email' => 'Please enter a valid email address.',
            'query.max' => 'The email address must not exceed 100 characters.',
            'recaptchaToken.required' => 'The security verification token is missing. Please try again.',
        ];

        $this->validate($rules, $messages);

        // Security check: Rate limit search queries to prevent scraping or database DoS
        $ip = request()->ip();
        $userId = auth()->id();
        $rateLimitKey = 'search-vendor:' . ($userId ? 'user:' . $userId : 'ip:' . $ip);

        if (\Illuminate\Support\Facades\RateLimiter::tooManyAttempts($rateLimitKey, 30)) {
            $seconds = \Illuminate\Support\Facades\RateLimiter::availableIn($rateLimitKey);
            $this->addError('query', "Too many verification searches. Please wait {$seconds} seconds before trying again.");
            return;
        }

        // Record a search hit
        \Illuminate\Support\Facades\RateLimiter::hit($rateLimitKey, 60);

        $searchResult = $searchService->verify($this->type, $this->query);
        
        $this->results = [
            'search_type' => $searchResult['search_type'],
            'normalized_query' => $searchResult['normalized_query'],
            'is_verified_safe' => $searchResult['is_verified_safe'],
            'safe_vendor' => $searchResult['safe_vendor'] ? $searchResult['safe_vendor']->toArray() : null,
            // Convert reports collection to array for serialization safety in Livewire
            'reports' => $searchResult['reports']->map(function ($report) {
                return [
                    'id' => $report->id,
                    'masked_account_number' => $report->masked_account_number,
                    'masked_phone_number' => $report->masked_phone_number,
                    'masked_email_address' => $report->masked_email_address,
                    'bank_name' => $report->bank_name,
                    'narrative' => $report->narrative,
                    'category_name' => $report->category ? $report->category->name : 'Unknown',
                    'reporter_pseudonym' => $report->user ? $report->user->pseudonym : 'Anonymous',
                    'credibility_score' => $report->weighted_credibility,
                    'ranking_score' => $report->ranking_score,
                    'created_at' => $report->created_at->diffForHumans(),
                ];
            })->toArray(),
            'match_type' => $searchResult['match_type'],
        ];

        $this->searched = true;
    }

    /**
     * Resets the search form and results.
     */
    public function resetSearch(): void
    {
        $this->query = '';
        $this->results = null;
        $this->searched = false;
    }

    public function render()
    {
        return view('livewire.search-vendor');
    }
}
