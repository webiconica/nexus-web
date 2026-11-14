<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Lead extends Model
{
    protected $fillable = ['tenant_id', 'origen', 'datos_formulario'];

    protected $casts = [
        'datos_formulario' => 'array',
    ];
}
