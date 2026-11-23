<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToTenant;

class Page extends Model
{
    use BelongsToTenant;
    protected $fillable = [
        'title',
        'slug',
        'content',
        'meta_title',
        'meta_description',
        'meta_keywords',
        'favicon_path',
        'og_image_path',
        'twitter_image_path',
        'head_scripts',
        'footer_scripts',
    ];
}
