<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

class NormalTokenLimiter
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {

        $userToken = request()->user()->currentAccessToken();
        $currentEndpoint = $request->getPathInfo();
        $cacheKey = 'normal-token-limiter:' . $currentEndpoint . ':' . $userToken->tokenable_id; //membuat key cache berdasarkan endpoint dan id user

        if($userToken->abilities[0] == 'normal-token')
        {
            $timeHits = Cache::get($cacheKey , 1);

            if($timeHits > 5000) // maksisal timehits ke masing-masing endpoint 5000 kali perhari
            {
                return response()->json([
                    'status' => false,
                    'message' => 'Sorry, you reach limit daily access to this endpoint :('
                ] , 429);
            }else{
                $timeHits++;
                Cache::put($cacheKey , $timeHits , now()->addDays(1));
            }
        }

        return $next($request);
    }
}
