<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsOwner
{
    /**
     * Handle an incoming request.
     * Enforces strict Owner-only access with HTTP 403 Forbidden.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! Auth::check() || ! Auth::user()->isOwner()) {
            abort(403, 'عذراً، هذه الصفحة أو الوظيفة مخصصة لمالك المتجر فقط ولا تملك الصلاحية للوصول إليها.');
        }

        return $next($request);
    }
}

