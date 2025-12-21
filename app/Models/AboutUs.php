<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AboutUs extends Model
{
    protected $table = 'about_us';

    protected $fillable = [
        'company_name',
        'phone',
        'description',
        'facebook_url',
        'instagram_url',
        'website_url',
        'whatsapp_url',
        'facebook_icon',
        'instagram_icon',
        'website_icon',
        'whatsapp_icon',
    ];
}
