<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class WebSessionAuth
{
    public function handle(Request $request, Closure $next): mixed
    {
        if (!session('usuario')) {
            return redirect('/login')->with('error', 'Faça login para continuar.');
        }

        return $next($request);
    }
}
