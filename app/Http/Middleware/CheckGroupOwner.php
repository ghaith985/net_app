<?php

namespace App\Http\Middleware;

use App\Models\Group;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckGroupOwner
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $group = Group::query()->find($request->group_id);
       // dd($group);
        $user= auth()->user();
        if($group->owner_id != $user->id) {
            return response()->json(['You dont owned group'], 403);
        }
        return $next($request);
    }
}
