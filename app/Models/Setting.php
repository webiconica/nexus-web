<?php

namespace App\Models;
use App\Traits\BelongsToTenant;


use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    use BelongsToTenant;
    // Permitimos la asignación masiva de estos campos
    protected $fillable = [
        'key', 
        'value', 
        'type'
    ];
}