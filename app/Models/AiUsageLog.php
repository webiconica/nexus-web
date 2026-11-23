<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToTenant;

class AiUsageLog extends Model
{
    use BelongsToTenant;
    protected $fillable = [
        'module',
        'model',
        'prompt_tokens',
        'completion_tokens',
        'total_tokens',
        'cost_usd',
        'cost_mxn',
    ];
}