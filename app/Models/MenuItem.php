<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class MenuItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'location',
        'title',
        'url',
        'target',
        'is_button',
        'parent_id',
        'sort_order',
    ];

    protected $casts = [
        'is_button' => 'boolean',
    ];

    // Relación de elementos hijos
    public function children()
    {
        return $this->hasMany(MenuItem::class, 'parent_id')->orderBy('sort_order', 'asc');
    }

    // Relación padre
    public function parent()
    {
        return $this->belongsTo(MenuItem::class, 'parent_id');
    }

    /**
     * Construye y formatea el árbol para el frontend ($headerMenu)
     */
    public static function getTree(string $location = 'header'): array
    {
        $items = self::where('location', $location)
            ->whereNull('parent_id')
            ->orderBy('sort_order', 'asc')
            ->with(['children'])
            ->get();

        return $items->map(function ($item) {
            return [
                'id' => $item->id,
                'title' => $item->title,
                'url' => $item->url ?? '#',
                'target' => $item->target ?? '_self',
                'is_button' => (bool)$item->is_button,
                'children' => $item->children->map(function ($child) {
                    return [
                        'id' => $child->id,
                        'title' => $child->title,
                        'url' => $child->url ?? '#',
                        'target' => $child->target ?? '_self',
                        'is_button' => (bool)$child->is_button,
                        'children' => []
                    ];
                })->toArray()
            ];
        })->toArray();
    }

    // Limpia la caché al crear, actualizar o borrar
    protected static function booted()
    {
        static::saved(function () {
            Cache::forget('header_menu_tree');
        });

        static::deleted(function () {
            Cache::forget('header_menu_tree');
        });
    }
}