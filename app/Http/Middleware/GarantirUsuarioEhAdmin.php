<?php

namespace App\Http\Middleware;

use App\Models\Inscricao;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class GarantirUsuarioEhAdmin
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::user()->isAdmin() == false) {
            return redirect()->route('inicio');
        }
        return $next($request);
    }
}
