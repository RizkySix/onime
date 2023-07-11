<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ChangePaymentMethodValidData
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $pricing_order = $request->route('pricing_order');
        if($pricing_order->user_id !== auth()->user()->id || $pricing_order->transaction_status !== 'pending'){
            abort(403);
        }
        return $next($request);
    }
}
