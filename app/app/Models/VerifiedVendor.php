<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VerifiedVendor extends Model
{
    use HasFactory;

    protected $table = 'verified_vendors';

    protected $fillable = [
        'business_name',
        'bank_account_number',
        'bank_name',
        'phone_number',
        'email_address',
        'verification_badge_url',
        'notes',
    ];
}
