<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PreventAdminActions
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
// تحقق مما إذا كان المستخدم الحالي هو أدمن
        if (auth()->check() && auth()->user()->is_admin) {
            return response()->json([
                'message' => 'Admins are not allowed to perform this action.'
            ], 403);
        }

        return $next($request);
    }
}
