<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Http;

class PostController extends Controller
{
    // 1. Mostrar la lista de artículos
    public function index()
    {
        // Traemos los artículos ordenados por el más reciente, de 10 en 10
        $posts = Post::latest()->paginate(10);
        return view('admin.posts.index', compact('posts'));
    }

    // 2. Mostrar el formulario para crear un artículo
    public function create()
    {
        return view('admin.posts.create');
    }

    // 3. Guardar el nuevo artículo
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'title' => 'required|max:255',
            'slug' => 'nullable|string|max:255|unique:posts,slug',
            'excerpt' => 'nullable|string',
            'content' => 'required|string',
            'featured_image' => 'nullable|string', // AHORA ES UN TEXTO (URL de la Biblioteca)
        ]);

        $validatedData['slug'] = $request->slug ? Str::slug($request->slug) : Str::slug($request->title);
        $validatedData['is_published'] = $request->has('is_published');
        $validatedData['published_at'] = $request->has('is_published') ? now() : null;

        // Como usamos la Biblioteca, simplemente insertamos los datos directo en la BD
        Post::create($validatedData);

		// 1. Le avisamos a Google que lea el sitemap (Solo si el post se publicó)
        /*
        if ($validatedData['is_published']) {
            \Illuminate\Support\Facades\Http::get('https://www.google.com/ping?sitemap=https://webiconica.com/sitemap.xml');
        }
        */

        return redirect()->route('admin.posts.index')->with('success', 'Artículo creado correctamente.');
    }

    // 4. Mostrar el formulario de edición con los datos del artículo
    public function edit(Post $post)
    {
        return view('admin.posts.edit', compact('post'));
    }

    // 5. Actualizar el artículo modificado
    public function update(Request $request, Post $post)
    {
        $validatedData = $request->validate([
            'title' => 'required|max:255',
            'slug' => 'required|string|max:255|unique:posts,slug,' . $post->id,
            'excerpt' => 'nullable|string',
            'content' => 'required|string',
            'featured_image' => 'nullable|string', // AHORA ES UN TEXTO (URL de la Biblioteca)
        ]);

        $validatedData['slug'] = $request->slug ? Str::slug($request->slug) : Str::slug($request->title);
        $validatedData['is_published'] = $request->has('is_published');
        
        // Lógica de fechas de publicación
        if ($validatedData['is_published'] && !$post->published_at) {
            $validatedData['published_at'] = now();
        } elseif (!$validatedData['is_published']) {
            $validatedData['published_at'] = null;
        }

        // Actualizamos directo, SIN borrar la imagen anterior del servidor
        $post->update($validatedData);

        return redirect()->route('admin.posts.index')->with('success', 'Artículo actualizado correctamente.');
    }

    // 6. Eliminar un artículo
    public function destroy(Post $post)
    {
        // Ya NO borramos la imagen física porque ahora pertenece a la Biblioteca de Medios central
        $post->delete();

        return redirect()->route('admin.posts.index')->with('success', 'Artículo eliminado.');
    }
}