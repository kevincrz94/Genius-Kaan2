<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class Admin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (session()->has('admin_id') && in_array(session('admin_role'), ['admin', 'super_admin'], true)) {
            return $next($request);
        }

        return redirect()->route('user.login')->with('error', 'Inicia sesión con perfil administrador para continuar.');
    }
}
