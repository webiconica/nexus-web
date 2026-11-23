<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Models\Tenant;
use App\Models\User;
use App\Services\TenantContext;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    public function create(): View
    {
        return view('auth.register');
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name'         => ['required', 'string', 'max:255'],
            'company_name' => ['required', 'string', 'max:150'],
            'email'        => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:' . User::class],
            'password'     => ['required', 'confirmed', Rules\Password::defaults()],
        ], [
            'company_name.required' => 'Por favor ingresa el nombre de tu empresa o proyecto.',
        ]);

        // Validación opcional de Cloudflare Turnstile si está configurado en .env
        $this->verifyTurnstile($request);

        $user = DB::transaction(function () use ($request) {
            $baseSlug = Str::slug($request->company_name) ?: 'empresa';
            $slug = $baseSlug . '-' . strtolower(Str::random(4));

            // 1. Crear el Tenant aislado en modo Chat + CRM
            $tenant = Tenant::create([
                'name'               => trim($request->company_name),
                'slug'               => $slug,
                'plan'               => 'starter',
                'monthly_chat_limit' => 500,
                'crm_only_mode'      => true,
                'is_active'          => true,
            ]);

            // Fijar el contexto del nuevo Tenant para que BelongsToTenant asigne su ID
            TenantContext::set($tenant);

            // 2. Configuraciones iniciales de marca y chatbot para este Tenant
            $initialSettings = [
                'site_name'       => $tenant->name,
                'contact_email'   => $request->email,
                'chatbot_name'    => 'Asistente ' . $tenant->name,
                'chatbot_welcome' => '¡Hola! 👋 Bienvenido a ' . $tenant->name . '. ¿En qué te podemos ayudar hoy?',
                'chatbot_context' => "Eres el asistente comercial de {$tenant->name}. Responde con amabilidad, resuelve dudas y solicita nombre y WhatsApp para cotizar o agendar.",
                'brand_dark'      => '#1A0B5E',
                'brand_light'     => '#6366F1',
            ];

            foreach ($initialSettings as $key => $value) {
                Setting::updateOrCreate(
                    ['tenant_id' => $tenant->id, 'key' => $key],
                    ['value' => $value]
                );
            }

            // 3. Crear el usuario vinculado al Tenant
            $user = User::create([
                'tenant_id' => $tenant->id,
                'name'      => trim($request->name),
                'email'     => strtolower(trim($request->email)),
                'password'  => Hash::make($request->password),
            ]);

            // Asignar rol de Spatie si existe en la base de datos
            if (class_exists(\Spatie\Permission\Models\Role::class)) {
                $role = \Spatie\Permission\Models\Role::whereIn('name', ['admin', 'cliente', 'client'])->first();
                if ($role) {
                    $user->assignRole($role);
                }
            }

            return $user;
        });

        event(new Registered($user));

        Auth::login($user);

        return redirect()->route('admin.crm.index');
    }

    protected function verifyTurnstile(Request $request): void
    {
        $secretKey = env('TURNSTILE_SECRET_KEY');
        if (empty($secretKey)) {
            return;
        }

        $token = $request->input('cf-turnstile-response');
        if (empty($token)) {
            throw ValidationException::withMessages([
                'email' => 'Por favor completa la verificación de seguridad antes de continuar.',
            ]);
        }

        $response = Http::asForm()->post('https://challenges.cloudflare.com/turnstile/v0/siteverify', [
            'secret'   => $secretKey,
            'response' => $token,
            'remoteip' => $request->ip(),
        ]);

        if (!$response->json('success')) {
            throw ValidationException::withMessages([
                'email' => 'La verificación de seguridad expiró o falló. Intenta de nuevo.',
            ]);
        }
    }
}