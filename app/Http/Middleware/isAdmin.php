<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Session;

class isAdmin
{
    /**
     
Handle an incoming request.*
@param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next*/
// public function handle(Request $request, Closure $next): Response
//     {
//         if (isset(Session::exists('role')['is_admin'])) {
//             if (Session::get('role')['is_admin']) {
//                 return $next($request);
//             }
//         }

//         return abort(403);
//     }

    public function handle(Request $request, Closure $next): Response
    {
        // Gunakan dot notation yang aman dan ringkas.
        // Session::get('role.is_admin') akan mengembalikan true jika ada, atau null/false jika tidak ada.
        if (Session::get('role.is_admin') === true) {
            return $next($request);
        }

        return abort(403, 'Akses Ditolak');
    }
}