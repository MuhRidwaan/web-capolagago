<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RedirectCustomerFromAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && ! $user->hasAnyRole(['Super Admin', 'Mitra'])) {
            return redirect()->route('frontend.profile');
        }

        return $next($request);
    }
}
