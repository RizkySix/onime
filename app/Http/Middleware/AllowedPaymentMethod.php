<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AllowedPaymentMethod
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {

        $data_order = json_decode($request->order , true);
      
        $vaPayment = 'unknown';

        if(isset($data_order['va_numbers'])){
            $vaPayment = $data_order['va_numbers'][0]['bank'];
        }elseif(isset($data_order['payment_code']) && !isset($data_order['va_numbers'])){
            $vaPayment = $data_order['payment_type'];
        }elseif(isset($data_order['payment_type'])){
            $vaPayment = $data_order['payment_type'];
        }

        $allowedPayment = ['bca' , 'bri' , 'bni' , 'cstore' , 'credit_card'];
        return !in_array($vaPayment , $allowedPayment) ? abort(400) : $next($request);
    }
}
