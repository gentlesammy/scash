<?php

namespace App\Rules;

use App\Services\RecaptchaService;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class Recaptcha implements ValidationRule
{
    protected string $action;

    /**
     * Create a new rule instance.
     *
     * @param string $action The action name to verify
     */
    public function __construct(string $action)
    {
        $this->action = $action;
    }

    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $recaptchaService = app(RecaptchaService::class);
        
        if (!$recaptchaService->verify($value, $this->action)) {
            $fail('The security verification failed. Please try again.');
        }
    }
}
