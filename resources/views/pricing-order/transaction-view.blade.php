<x-guest-layout>
      <!-- @TODO: replace SET_YOUR_CLIENT_KEY_HERE with your client key -->
            <script type="text/javascript"
              src="https://app.sandbox.midtrans.com/snap/snap.js"
              data-client-key="SB-Mid-client-c9AMdNDmZhCjm1sK"></script>
            <!-- Note: replace with src="https://app.midtrans.com/snap/snap.js" for Production environment -->
        <div>
            {{ $pricing->pricing_name }} <br>
            <label for="">Normal price : {{ $pricing->price }}</label> <br>
            @php
                $getDiscount = $pricing->price * ($pricing->discount / 100);
                $discountPrice = $pricing->price - $getDiscount;
            @endphp
            <label for="">Diskon price : {{ $discountPrice }}</label> <br>

            <button id="pay-button">Pay!</button>
            
            <script type="text/javascript">
              // For example trigger on button clicked, or any time you need
              var payButton = document.getElementById('pay-button');
              payButton.addEventListener('click', function () {
                // Trigger snap popup. @TODO: Replace TRANSACTION_TOKEN_HERE with your transaction token
                window.snap.pay({{ $snapToken }});
                // customer will be redirected after completing payment pop-up
              });
            </script>
        </div>
 </x-guest-layout>