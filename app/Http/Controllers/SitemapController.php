<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\Page; // <-- Agrega esta línea para importar tus páginas
use Illuminate\Http\Request;

class SitemapController extends Controller
{
    public function index()
    {
        $posts = Post::where('is_published', true)
                     ->orderBy('updated_at', 'desc')
                     ->get();

        // <-- Descomentamos esta línea para traer tus páginas
        $pages = Page::orderBy('updated_at', 'desc')->get();

        $xmlHeader = '<?xml version="1.0" encoding="UTF-8"?>';

        return response()
            // <-- Agregamos 'pages' al compact
            ->view('sitemap', compact('posts', 'pages', 'xmlHeader')) 
            ->header('Content-Type', 'text/xml');
    }
}