<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    // Permitimos la asignación masiva de estos campos
    protected $fillable = [
        'key', 
        'value', 
        'type'
    ];
}