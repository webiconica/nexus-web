<?php

namespace App\Http\Controllers;

use App\Models\Page;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PageController extends Controller
{
    // 1. Vista pública para renderizar la landing dinámica
    public function show($slug)
    {
        $page = Page::where('slug', $slug)->firstOrFail();
        return view('dynamic-page', compact('page'));
    }

    // 2. Listado de páginas en el panel de administración
    public function index()
    {
        $pages = Page::latest()->paginate(10);
        return view('pages.index', compact('pages'));
    }

    // 3. Formulario de creación
    public function create()
    {
        return view('pages.create');
    }

    // 4. Guardar la nueva página en la BD
    public function store(Request $request)
    {
        // 1. Validamos todos los campos como texto (incluyendo las rutas de imágenes y scripts)
        $validatedData = $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:pages,slug',
            'content' => 'nullable|string',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string',
            'meta_keywords' => 'nullable|string',
            'favicon_path' => 'nullable|string', // Ahora es un simple texto (URL)
            'og_image_path' => 'nullable|string', // Ahora es un simple texto (URL)
            'head_scripts' => 'nullable|string',
            'footer_scripts' => 'nullable|string',
        ]);

        // 2. Generar slug automático si el usuario lo dejó vacío
        $validatedData['slug'] = $request->slug ? Str::slug($request->slug) : Str::slug($request->title);

        // 3. Como ya no hay archivos físicos que procesar, simplemente creamos el registro
        Page::create($validatedData);

        return redirect()->route('admin.pages.index')->with('status', 'pagina-creada');
    }

    // 5. Formulario de edición
    public function edit(Page $page)
    {
        return view('pages.edit', compact('page'));
    }

    // 6. Actualizar la página existente
    public function update(Request $request, Page $page)
    {
        $validatedData = $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:pages,slug,' . $page->id,
            'content' => 'nullable|string',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string',
            'meta_keywords' => 'nullable|string',
            'favicon_path' => 'nullable|string', // Ahora es un simple texto (URL)
            'og_image_path' => 'nullable|string', // Ahora es un simple texto (URL)
            'head_scripts' => 'nullable|string',
            'footer_scripts' => 'nullable|string',
        ]);

        // Formateamos el slug para que siempre sea amigable
        $validatedData['slug'] = Str::slug($request->slug);

        // Actualizamos la base de datos (sin borrar nada del Storage)
        $page->update($validatedData);

        return redirect()->route('admin.pages.index')->with('status', 'pagina-actualizada');
    }

    // 7. Eliminar página
    public function destroy(Page $page)
    {
        // Ya NO borramos las imágenes del Storage porque pertenecen a la Biblioteca de Medios
        // y podrían estar siendo utilizadas por otras páginas o entradas de blog.
        
        $page->delete();

        return redirect()->route('admin.pages.index')->with('status', 'pagina-eliminada');
    }
}