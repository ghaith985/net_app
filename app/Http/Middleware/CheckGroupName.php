<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Group;
class CheckGroupName
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        //find group name from request->name and prevent duplicated name
       // dd($request);
        $group = $request->name;
       // dd($group);
        $group_name = Group::where('name', $group)->first();
        if($group_name){
            return response()->json(['message' => 'Group name already exist'], 409);
        }
        return $next($request);
    }
}
