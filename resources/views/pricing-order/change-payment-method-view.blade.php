<x-bootsrap.main-view title="Payment Change Method">
  <!-- @TODO: replace SET_YOUR_CLIENT_KEY_HERE with your client key -->
  <script type="text/javascript"
  src="https://app.sandbox.midtrans.com/snap/snap.js"
  data-client-key="{{ env('MIDTRANS_CLIENTKEY') }}"></script>
<!-- Note: replace with src="https://app.midtrans.com/snap/snap.js" for Production environment -->


   <div class="container">
     <div class="row d-flex vh-100 justify-content-center align-items-center">
      <div class="col-sm-4">
        <div class="card" style="width: 24rem;">
          <img src="/asset-img/soft-bg.jpg" class="card-img-top" alt="rusak">
          <div class="card-body d-flex flex-column">
            <h3 class="card-title">{{ $pricing_order->pricing_type }}</h3>
            <p class="card-text">
              <hr>
              <span class="text-center text-muted">Merubah metode pembayaran akan merubah Order ID pesanan</span>
            </p>
           <div class="bottom mt-auto text-center" style="">
              <hr>
              <span class="fw-bold">{{ $pricing_order->pricing_duration_in_days }} Hari Berlangganan!</span>
              <div class="pricing">
              @php
                  $getDiscount = $pricing_order->pricing_price * ($pricing_order->pricing_discount / 100);
                  $discountPrice = $pricing_order->pricing_price - $getDiscount;
              @endphp

              <strike>
                  Rp. {{ number_format($pricing_order->pricing_price) }}
              </strike> <br>
              <span class="fw-bold" style="padding:5px;">
                  Rp. {{ number_format($discountPrice) }}
              </span>
              
              <div class="mt-4">
                <x-bootsrap.payment-button type="" id="pay-button">
                  METODE PEMBAYARAN
                </x-bootsrap.payment-button>
              </div>

              <form action="/change-payment-method/{{ $pricing_order->order_id }}" method="POST" id="form-order">
                @csrf
                @method('put')
                <input type="hidden" name="order" id="input-order" readonly>
              </form>
              </div>
           </div>
          </div>
        </div>
      </div>
     </div>
  </div>

  <script type="text/javascript">
    // For example trigger on button clicked, or any time you need
    var payButton = document.getElementById('pay-button');
    payButton.addEventListener('click', function () {
      // Trigger snap popup. @TODO: Replace TRANSACTION_TOKEN_HERE with your transaction token
      window.snap.pay('{{ $snapToken }}', {
        onSuccess: function(result){
          /* You may add your own implementation here */
        
          result = JSON.stringify(result);
         document.getElementById('input-order').value = result;
         document.getElementById('form-order').submit();
        },
        onPending: function(result){
          /* You may add your own implementation here */
       
          result = JSON.stringify(result);
         document.getElementById('input-order').value = result;
         document.getElementById('form-order').submit();
        },
        onError: function(result){
          /* You may add your own implementation here */
        
        },
        onClose: function(){
          /* You may add your own implementation here */
         
        }
      })
    });
  </script>
</x-bootsrap.main-view>
