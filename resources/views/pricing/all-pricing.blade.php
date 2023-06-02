<x-guest-layout>
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
                <form action="/pricing-restore/{{ $pricing->pricing_name }}" method="POST">
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


            @endif

        </div>
    @endforeach
 </x-guest-layout>