<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Session;
class DepartmentPermissionMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    // public function handle($request, Closure $next, $role, $departmentId)
    // {
    //     $user = auth()->user();
    //     dd($user);
    //     if (!$user->departments->contains($departmentId)) {
    //         abort(403, 'Unauthorized');
    //     }

    //     $departmentRole = $user->departments->where('id', $departmentId)->first()->pivot->role ?? null;

    //     if ($departmentRole !== $role) {
    //         abort(403, 'Unauthorized');
    //     }

    //     return $next($request);
    // }
}
