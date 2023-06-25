<x-bootsrap.main-view title="Payment Success">
   
     <div class="container">
       <div class="row d-flex vh-100 justify-content-center align-items-center">
        <div class="col-sm-4">
          <div class="card" style="width: 24rem;">
            <div class="card-body d-flex flex-column">
              <h3 class="card-title text-center">{{ $order->pricing_type }}</h3>
              <p class="card-text text-center">
                <span class="text-muted">
                    {{ strtoupper($order->transaction_status) }}
                </span>
                <p class="text-muted text-center">
                    @if ($order->transaction_status != 'settlement' || $order->transaction_status != 'capture')
                    Segera lakukan pembayaran ke <span class="fw-bold">{{ strtoupper($order->payment_type) }}</span> dengan nomor pembayaran <span class="fw-bold">{{ strtoupper($order->payment_number) }}</span>

                    @else
                    Transaksi Anda Berhasil!
                    @endif
                </p>

                <div class="ms-auto" style="font-size: 12px;">
                  @if (\Carbon\Carbon::parse($order->transaction_time)->addHours(24)->diffInHours() >= 1)
                  <span class="text-muted">Pembayaran gagal dalam <span class="fw-bold">{{ \Carbon\Carbon::parse($order->transaction_time)->addHours(24)->diffInHours() }} jam</span></span>
                  @else
                  <span class="text-muted">Pembayaran gagal dalam <span class="fw-bold">{{ \Carbon\Carbon::parse($order->transaction_time)->addHours(24)->diffInMinutes() }} menit</span></span>
                  @endif
                </div>
                <hr>
                <div class="text-center">
                    <span>Order Id :</span> <br>
                    <span class="fw-bold">{{ $order->order_id }}</span><br>
                    <span>Metode Pembayaran :</span> <br>
                    <span class="fw-bold">{{ strtoupper($order->payment_type) }}</span>
                    @if ($order->transaction_status == 'pending')
                    <form action="/change-payment-method/{{ $order->order_id }}/edit" method="GET" class="mt-4" >
                      <x-bootsrap.payment-button type="sumbit">
                         UBAH METODE PEMBAYARAN
                      </x-bootsrap.payment-button>
                      </form>
                    @endif
                </div>
               
              </p>
             <div class="bottom mt-auto text-center" style="">
                <hr>
                <span class="fw-bold">{{ $order->pricing_duration_in_days }} Hari Berlangganan!</span>
                <div class="pricing">
                @php
                    $getDiscount = $order->pricing_price * ($order->pricing_discount / 100);
                    $discountPrice = $order->pricing_price - $getDiscount;
                @endphp
  
                <strike>
                    Rp. {{ number_format($order->pricing_price) }}
                </strike> <br>
                <span class="fw-bold" style="padding:5px;">
                    Rp. {{ number_format($discountPrice) }}
                </span>
                
                <div class="mt-4">
                    <form action="/dashboard" method="GET">
                        <x-bootsrap.main-button type="submit">
                            KONFIRMASI
                        </x-bootsrap.main-button>
                    </form>
                </div>
                </div>
             </div>
            </div>
          </div>
        </div>
       </div>
    </div>
  
   
  </x-bootsrap.main-view>
  