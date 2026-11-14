<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\Request;

class BlogController extends Controller
{
    // Mostrar la página principal del blog con todos los artículos
    public function index()
    {
        // Solo traemos los que marcaste como "publicados"
        $posts = Post::where('is_published', true)
                     ->latest('published_at')
                     ->paginate(9); // Muestra 9 artículos por página (ideal para un grid 3x3)
                     
        return view('blog.index', compact('posts'));
    }

    // Mostrar un artículo completo
    public function show($slug)
    {
        // Buscamos el artículo por su slug. Si no existe o no está publicado, da un error 404
        $post = Post::where('slug', $slug)
                    ->where('is_published', true)
                    ->firstOrFail();
                    
        return view('blog.show', compact('post'));
    }
}