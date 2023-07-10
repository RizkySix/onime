<x-bootsrap.main-view title="User Order">
    <x-bootsrap.navbar>
    </x-bootsrap.navbar>
    
    <div class="container" style="margin-top: 50px;">
       @if (session('status'))
       <h4 class="fw-bold text-center mb-4" style="color:gray">
        {{ session('status') }}
    </h4>
       @endif
      <div class="row d-flex vh-100 justify-content-center align-items-center" >
      @foreach ($orders as $order)
      <div class="col-sm-4 mb-3">
        <div class="card" style="width: 24rem;">
          <div class="card-body d-flex flex-column">
            <h3 class="card-title text-center">{{ $order->pricing_type }}</h3>
            <p class="card-text text-center">
              <span class="text-muted">
                  {{ strtoupper($order->transaction_status) }}
              </span>
              <p class="text-muted text-center">
                @if($order->transaction_status == 'cancel')
                <span class="fw-bold">Order ini sudah dibatalkan</span>
                  @elseif ($order->transaction_status != 'settlement' && $order->transaction_status != 'capture')
                  Segera lakukan pembayaran ke <span class="fw-bold">{{ strtoupper($order->payment_type) }}</span> dengan nomor pembayaran <span class="fw-bold">{{ strtoupper($order->payment_number) }}</span>

                  @else
                  Transaksi Anda Berhasil!
                  @endif
              </p>

              @if ($order->transaction_status != 'settlement' && $order->transaction_status != 'capture')
              <div class="ms-auto" style="font-size: 12px;">
                @if($order->transaction_status ==  'cancel')
                <span class="text-muted">Order dibatalkan</span>
                @elseif (\Carbon\Carbon::parse($order->transaction_time)->addHours(24)->diffInHours() >= 1)
                <span class="text-muted">Pembayaran gagal dalam <span class="fw-bold">{{ \Carbon\Carbon::parse($order->transaction_time)->addHours(24)->diffInHours() }} jam</span></span>
                @elseif(\Carbon\Carbon::parse($order->transaction_time)->addHours(24) <= \Carbon\Carbon::now())
                <span class="text-muted">Order telah expired (gagal)</span></span>
                @else
                <span class="text-muted">Pembayaran gagal dalam <span class="fw-bold">{{ \Carbon\Carbon::parse($order->transaction_time)->addHours(24)->diffInMinutes() }} menit</span></span>
                @endif
              </div>
              @endif
              <hr>
              <div class="text-center">
                  <span>Order Id :</span> <br>
                  <span class="fw-bold">{{ $order->order_id }}</span><br>
                  <span>Metode Pembayaran :</span> <br>
                  <span class="fw-bold">{{ strtoupper($order->payment_type) }}</span>
                  @if ($order->payment_type == 'cstore')
                    <br>
                        <span class="text-muted">(Indomaret)</span>
                    @endif
                  @if ($order->transaction_status == 'pending')
                  <form action="/change-payment-method/{{ $order->order_id }}/edit" method="GET" class="mt-4" >
                    <x-bootsrap.payment-button type="sumbit">
                       UBAH METODE PEMBAYARAN
                    </x-bootsrap.payment-button>
                    </form>
                    <form action="/cancel-order/{{ $order->order_id }}" method="POST" class="mt-4">
                        @csrf
                        <x-bootsrap.main-button type="sumbit">
                           CANCEL ORDER
                         </x-bootsrap.main-button>
                         </form>
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
                @if ($order->transaction_status == 'cancel')
              <form action="{{ route('cancel-order-delete' , $order->order_id) }}" method="POST" class="mt-4">
                @csrf
                @method('delete')
                <x-bootsrap.main-button type="sumbit">
                  DELETE
                 </x-bootsrap.main-button>
                 </form>
                @else
                <form action="/dashboard" method="GET">
                    <x-bootsrap.main-button type="submit">
                        KONFIRMASI
                    </x-bootsrap.main-button>
                </form>
              @endif
                  
              </div>
              </div>
           </div>
          </div>
        </div>
      </div>
      @endforeach
      </div>
   </div>
 
  
 </x-bootsrap.main-view>
 