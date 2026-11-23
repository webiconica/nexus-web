<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\View\View;

class TenantController extends Controller
{
    public function index(): View
    {
        $tenants = Tenant::orderBy('id')->get();
        return view('admin.tenants.index', compact('tenants'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name'               => 'required|string|max:150',
            'custom_domain'      => 'nullable|string|max:150|unique:tenants,custom_domain',
            'plan'               => 'required|in:emprendedor,negocio,pro',
            'monthly_chat_limit' => 'required|integer|min:100|max:100000',
            'admin_name'         => 'required|string|max:120',
            'admin_email'        => 'required|email|unique:users,email',
            'admin_password'     => 'required|string|min:8',
        ]);

        $tenant = Tenant::create([
            'name'               => $validated['name'],
            'slug'               => Str::slug($validated['name']) . '-' . Str::lower(Str::random(4)),
            'custom_domain'      => !empty($validated['custom_domain']) ? $validated['custom_domain'] : null,
            'allowed_origins'    => ['*'],
            'plan'               => $validated['plan'],
            'monthly_chat_limit' => $validated['monthly_chat_limit'],
            'crm_only_mode'      => $request->has('crm_only_mode'),
            'is_active'          => true,
        ]);

        User::create([
            'tenant_id' => $tenant->id,
            'name'      => $validated['admin_name'],
            'email'     => $validated['admin_email'],
            'password'  => Hash::make($validated['admin_password']),
        ]);

        return back()->with('success', "Cliente «{$tenant->name}» creado con API Key: {$tenant->api_key}");
    }

    public function toggleStatus(Tenant $tenant): RedirectResponse
    {
        if ($tenant->id === 1) {
            return back()->with('error', 'No se puede suspender el Tenant principal.');
        }

        $tenant->update(['is_active' => !$tenant->is_active]);
        return back()->with('success', 'Estado del cliente actualizado.');
    }
}