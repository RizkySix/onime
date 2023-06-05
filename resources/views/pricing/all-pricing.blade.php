<x-guest-layout>
    @if ($user_order)
      @foreach ($user_order as $order)
      {{ $order->id }} <br>
      {{ $order->order_id }} <br>
      {{ $order->transaction_status }} <br>
      {{ $order->gross_amount }} <br><br>

      @if (session('status'))
          {{ session('status') }}
      @endif

      @if ($order->transaction_status == 'pending')   
      <form action="/cancel-order/{{ $order->order_id }}" method="POST">
          @csrf
          <x-primary-button>
              {{ __('Cancel') }}
          </x-primary-button>
      </form>
      <form action="/change-payment-method/{{ $order->order_id }}/edit" method="GET">
        <x-primary-button>
            {{ __('Change payment') }}
        </x-primary-button>
        </form>
      <br><br>
      @endif

      @if ($order->transaction_status == 'expire' && $order->transaction_time < \Carbon\Carbon::now())   
      <form action="/change-payment-method/{{ $order->order_id }}/edit" method="GET">
        <x-primary-button>
            {{ __('Change payment') }}
        </x-primary-button>
        </form><br>
        <p>Jika tidak segera memilih metode pembayaran, pesanan akan dibatalkan secara otomatis dalam 24 jam</p>
      <br><br>
     
    @endif

      @endforeach
    @endif

  

    @foreach ($pricings as $pricing)
        <div>
            {{ $pricing->pricing_name }} <br>
            <label for="">Normal price : {{ $pricing->price }}</label> <br>
            @php
                $getDiscount = $pricing->price * ($pricing->discount / 100);
                $discountPrice = $pricing->price - $getDiscount;
            @endphp
            <label for="">Diskon price : {{ $discountPrice }}</label> <br>
            <a href="/pricing/{{ $pricing->pricing_name }}/edit">Edit</a><br>
            @if ($pricing->trashed())
                <form action="/pricing-restore/{{ $pricing->pricing_name }}" method="POST" >
                    @csrf
                    <x-primary-button>
                        {{ __('Restore') }}
                    </x-primary-button>
                </form> <br>
                <form action="/pricing-force-delete/{{ $pricing->pricing_name }}" method="POST">
                    @csrf
                    <x-primary-button>
                        {{ __('Force Delete') }}
                    </x-primary-button>
                </form>
                </form>

               
            @else
            <form action="/pricing/{{ $pricing->pricing_name }}" method="POST">
                @csrf
                @method('delete')
                <x-primary-button>
                    {{ __('Delete') }}
                </x-primary-button>
            </form> <br>

            <form action="/transaction-view/{{ $pricing->pricing_name }}" method="GET">
                <x-primary-button>
                    {{ __('Buy') }}
                </x-primary-button>
            </form>
            @if (session('payment-success'))
                {{ session('payment-success') }}
            @endif

            @endif

        </div>
    @endforeach
 </x-guest-layout>