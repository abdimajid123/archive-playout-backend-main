<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckTokenExpiry
{
    public function handle(Request $request, Closure $next)
    {
        $accessToken = $request->user()?->currentAccessToken();

        // If it's a wrapper (NewAccessToken), get the actual model
        $tokenModel = method_exists($accessToken, 'token') ? $accessToken->token : $accessToken;

        if ($tokenModel && $tokenModel->expires_at && now()->greaterThan($tokenModel->expires_at)) {
            $tokenModel->delete(); // revoke it
            return response()->json([
                'message' => 'Your token has expired. Please log in again.'
            ], 401);
        }

        return $next($request);
    }
}
