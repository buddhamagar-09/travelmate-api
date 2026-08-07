<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!$request->user()) {
            return response()->json([
                'message' => 'Unauthenticated'
            ], 401);
        }

        if ($request->user()->usertype !== 'admin') {
            return response()->json([
                'message' => 'Unauthorized xas ta'
            ], 403);
        }

        return $next($request);
    }
}

//AdminMiddleware checks if the authenticated user has an 'admin' usertype. 
//If the user is not authenticated, it returns a 401 Unauthenticated response. 
//If the user is authenticated but does not have the 'admin' usertype, it returns a 403 Unauthorized response. 
//If the user is an admin, the request proceeds to the next middleware or controller.