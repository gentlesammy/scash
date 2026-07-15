<?php

namespace App\Livewire;

use App\Models\Evidence;
use App\Models\Report;
use App\Models\ScamCategory;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Livewire\Component;
use Livewire\WithFileUploads;

class SubmitReport extends Component
{
    use WithFileUploads;

    public int $step = 1;

    // Step 1 Fields
    public ?int $scam_category_id = null;
    public string $vendor_type = 'bank';
    public string $vendor_value = '';
    public string $bank_name = '';
    public $original_image; // Temporary uploaded image
    public ?string $redacted_image_data = null; // Base64 data from client redaction canvas
    public int $duplicates_count = 0;
    public string $recaptchaToken = '';

    // Step 2 Fields
    public string $narrative = '';
    public array $screenshot_uploads = []; // Additional chat screenshots
    
    // Created Report State
    public ?int $report_id = null;

    /**
     * Listeners to capture real-time duplicate checks.
     */
    public function updatedVendorValue(): void
    {
        $this->checkForDuplicates();
    }

    public function updatedVendorType(): void
    {
        $this->vendor_value = '';
        $this->duplicates_count = 0;
    }

    /**
     * Checks if the vendor identifier is already reported in the database.
     */
    public function checkForDuplicates(): void
    {
        $cleaned = trim($this->vendor_value);
        if (strlen($cleaned) < 3) {
            $this->duplicates_count = 0;
            return;
        }

        // Normalize
        if ($this->vendor_type === 'bank') {
            $cleaned = preg_replace('/\s+|-/', '', $cleaned);
            $this->duplicates_count = Report::where('bank_account_number', $cleaned)->count();
        } elseif ($this->vendor_type === 'phone') {
            $cleaned = preg_replace('/[^\d+]/', '', $cleaned);
            $this->duplicates_count = Report::where('phone_number', $cleaned)->count();
        } else {
            $cleaned = strtolower($cleaned);
            $this->duplicates_count = Report::where('email_address', $cleaned)->count();
        }
    }

    /**
     * Handles Step 1 submission.
     * Validates, saves original and redacted images, and creates the report.
     */
    public function submitStepOne(): void
    {
        // Enforce route security gates at the controller action level
        $user = auth()->user();
        if (!$user || !$user->isFullyVerified() || $user->is_banned) {
            abort(403, 'Unauthorized access. Verification required.');
        }

        $rules = [
            'scam_category_id' => 'required|exists:scam_categories,id',
            'vendor_type' => 'required|in:bank,phone,email',
            'vendor_value' => 'required|string|min:3|max:100',
            'original_image' => 'required|image|max:5120|mimes:jpeg,png,webp', // Max 5MB
            'recaptchaToken' => ['required', new \App\Rules\Recaptcha('submit_report')],
        ];

        if ($this->vendor_type === 'bank') {
            $rules['bank_name'] = 'required|string|min:2|max:100';
        }

        $this->validate($rules, [
            'recaptchaToken.required' => 'The security verification token is missing. Please try again.',
        ]);

        // 1. Save Original receipt file privately
        $originalFilename = 'original_' . Str::uuid() . '.' . $this->original_image->getClientOriginalExtension();
        $originalPath = $this->original_image->storeAs('evidence/original', $originalFilename, 'local');

        // 2. Save Redacted receipt version (from Canvas base64 data)
        $redactedPath = null;
        if (!empty($this->redacted_image_data)) {
            $redactedPath = $this->saveBase64Image($this->redacted_image_data, 'evidence/redacted');
        }

        // 3. Create the Report (Stage 1)
        $reportData = [
            'user_id' => $user->id,
            'scam_category_id' => $this->scam_category_id,
            'bank_account_number' => $this->vendor_type === 'bank' ? $this->vendor_value : null,
            'bank_name' => $this->vendor_type === 'bank' ? $this->bank_name : null,
            'phone_number' => $this->vendor_type === 'phone' ? $this->vendor_value : null,
            'email_address' => $this->vendor_type === 'email' ? $this->vendor_value : null,
            'stage' => 'stage_1',
            'weighted_credibility' => 0.00,
            'ranking_score' => 0.00,
        ];

        $report = Report::create($reportData);
        $this->report_id = $report->id;

        // 4. Create the Evidence record
        Evidence::create([
            'report_id' => $report->id,
            'type' => 'receipt',
            'file_path' => $originalPath,
            'redacted_file_path' => $redactedPath ?? $originalPath, // fallback to original if no redaction was made
        ]);

        // Award default points for Stage 1 submission (+10 TP)
        $user->increment('trust_points', 10);

        // Move to Stage 2 wizard
        $this->step = 2;
    }

    /**
     * Handles Step 2 submission (progressive completion).
     */
    public function submitStepTwo(): mixed
    {
        $user = auth()->user();
        if (!$user || !$user->isFullyVerified() || $user->is_banned) {
            abort(403, 'Unauthorized.');
        }

        $this->validate([
            'narrative' => 'required|string|min:15|max:500',
            'screenshot_uploads' => 'nullable|array|max:3',
            'screenshot_uploads.*' => 'image|max:5120|mimes:jpeg,png,webp', // Max 5MB
        ]);

        $report = Report::findOrFail($this->report_id);
        $report->update([
            'narrative' => $this->narrative,
            'stage' => 'stage_2',
        ]);

        // Save screenshots if uploaded
        if (!empty($this->screenshot_uploads)) {
            foreach ($this->screenshot_uploads as $screenshot) {
                $filename = 'screenshot_' . Str::uuid() . '.' . $screenshot->getClientOriginalExtension();
                $path = $screenshot->storeAs('evidence/screenshots', $filename, 'local');

                Evidence::create([
                    'report_id' => $report->id,
                    'type' => 'screenshot',
                    'file_path' => $path,
                    'redacted_file_path' => $path, // Screenshots are default public
                ]);
            }
        }

        // Award bonus Trust Points for Stage 2 narrative detail (+15 TP)
        $user->increment('trust_points', 15);

        session()->flash('success', 'Thank you! Your fraud report has been successfully logged. +25 Total Trust Points earned.');

        return redirect()->route('dashboard');
    }

    /**
     * Decode and save base64 client-side compressed/redacted canvas image.
     */
    private function saveBase64Image(string $base64Data, string $directory): string
    {
        // Extract type and data
        if (preg_match('/^data:image\/(\w+);base64,/', $base64Data, $type)) {
            $data = substr($base64Data, strpos($base64Data, ',') + 1);
            $type = strtolower($type[1]); // png, jpeg, webp

            if (!in_array($type, ['jpg', 'jpeg', 'png', 'webp'])) {
                throw new \InvalidArgumentException('Invalid image type uploaded.');
            }

            $data = base64_decode($data);

            if ($data === false) {
                throw new \RuntimeException('Base64 decode failed.');
            }
        } else {
            throw new \InvalidArgumentException('Invalid base64 data format.');
        }

        $filename = 'redacted_' . Str::uuid() . '.' . $type;
        $path = $directory . '/' . $filename;

        Storage::disk('local')->put($path, $data);

        return $path;
    }

    public function render()
    {
        return view('livewire.submit-report', [
            'categories' => ScamCategory::all(),
        ]);
    }
}
