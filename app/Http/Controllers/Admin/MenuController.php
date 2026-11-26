<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MenuItem;
use App\Models\Page;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class MenuController extends Controller
{
    public function index()
    {
        $menuItems = MenuItem::whereNull('parent_id')
            ->orderBy('sort_order', 'asc')
            ->with('children')
            ->get();

        // Obtenemos páginas y posts para seleccionarlos en 1 clic
        $pages = Page::select('id', 'title', 'slug')->get();
        $posts = Post::select('id', 'title', 'slug')->latest()->take(20)->get();

        return view('admin.menu.index', compact('menuItems', 'pages', 'posts'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:100',
            'url' => 'required|string|max:255',
            'target' => 'required|in:_self,_blank',
            'parent_id' => 'nullable|exists:menu_items,id',
            'is_button' => 'nullable|boolean',
        ]);

        $validated['is_button'] = $request->has('is_button');
        $validated['location'] = 'header';
        $validated['sort_order'] = MenuItem::where('parent_id', $request->parent_id)->max('sort_order') + 1;

        MenuItem::create($validated);
        Cache::forget('header_menu_tree');
        Cache::forget('header_menu_tree_tenant_' . \App\Services\TenantContext::id());

        return redirect()->route('admin.menu.index')->with('success', 'Elemento añadido al menú correctamente.');
    }

    public function update(Request $request, MenuItem $menuItem)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:100',
            'url' => 'required|string|max:255',
            'target' => 'required|in:_self,_blank',
            'is_button' => 'nullable|boolean',
        ]);

        $validated['is_button'] = $request->has('is_button');
        $menuItem->update($validated);
        Cache::forget('header_menu_tree');
        Cache::forget('header_menu_tree_tenant_' . \App\Services\TenantContext::id());

        return redirect()->route('admin.menu.index')->with('success', 'Elemento actualizado correctamente.');
    }

    public function destroy(MenuItem $menuItem)
    {
        $menuItem->delete();
        Cache::forget('header_menu_tree');
        Cache::forget('header_menu_tree_tenant_' . \App\Services\TenantContext::id());

        return redirect()->route('admin.menu.index')->with('success', 'Elemento eliminado del menú.');
    }

    public function reorder(Request $request)
    {
        $items = $request->input('items', []);

        foreach ($items as $index => $itemData) {
            MenuItem::where('id', $itemData['id'])->update([
                'sort_order' => $index,
                'parent_id' => $itemData['parent_id'] ?? null
            ]);
        }

        Cache::forget('header_menu_tree');
        Cache::forget('header_menu_tree_tenant_' . \App\Services\TenantContext::id());

        return response()->json(['status' => 'success']);
    }
}