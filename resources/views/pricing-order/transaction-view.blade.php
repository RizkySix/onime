<x-guest-layout>
      <!-- @TODO: replace SET_YOUR_CLIENT_KEY_HERE with your client key -->
            <script type="text/javascript"
              src="https://app.sandbox.midtrans.com/snap/snap.js"
              data-client-key="{{ env('MIDTRANS_CLIENTKEY') }}"></script>
            <!-- Note: replace with src="https://app.midtrans.com/snap/snap.js" for Production environment -->
        <div>
            {{ $pricing->pricing_name }} <br>
            <label for="">Normal price : {{ $pricing->price }}</label> <br>
            @php
                $getDiscount = $pricing->price * ($pricing->discount / 100);
                $discountPrice = $pricing->price - $getDiscount;
            @endphp
            <label for="">Diskon price : {{ $discountPrice }}</label> <br>

            <x-primary-button id="pay-button">
                {{ __('Pay') }}
            </x-primary-button>

            <form action="/transaction/{{ $pricing->pricing_name }}" method="POST" id="form-order">
              @csrf
              <input type="text" name="order" value="" id="input-order">
            </form>
            
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
        </div>
 </x-guest-layout>