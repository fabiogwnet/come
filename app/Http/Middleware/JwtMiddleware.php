<?php

namespace App\Http\Middleware;

use Closure;
use Exception;
use App\Services\JWTService;

class JwtMiddleware
{
    public function handle($request, Closure $next, $guard = null)
    {
        if ($request->header('Authorization')) {
            $authorization = $request->header('Authorization');
        } else {
            $authorization = $request->session()->get('Authorization');
        }

        if ($authorization) {
            [$bearer, $jwtToken] = explode(' ', $authorization);

            $jwtService = new JWTService();
            try {
                if ($jwtService->validateToken($jwtToken)) {
                    $parser = $jwtService->getParserToken($jwtToken);

                    $request->jwtAuth = [
                        'uid' => $parser->claims()->get('uid'),
                    ];

                    return $next($request);
                }
            } catch (Exception $e) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Unauthorized or invalid access'
                ], 400);
            }

            $request->session()->flush();

            return response()->json([
                'status' => 'error',
                'message' => 'Your session has expired!'
            ], 401);
        }

        return response()->json([
            'status' => 'error',
            'message' => 'Unauthorized or invalid access'
        ], 400);
    }
}
