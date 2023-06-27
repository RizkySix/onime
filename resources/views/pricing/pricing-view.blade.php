<x-bootsrap.main-view title="Pricing List">
 
    <x-bootsrap.navbar>
    </x-bootsrap.navbar>

    <div class="container" style="margin-top: 50px;">
        <div class="row">
            <div class="col-sm-12 m-auto">
                <div class="header mb-4">
                 <h3 class="text-center">
                    Pricing List
                 </h3>
                </div>

                <div class="pricing-list d-flex justify-content-around flex-wrap mb-4">
                  @foreach ($pricings as $pricing)
                  <div class="card mb-4" style="width: 24rem;">
                    <img src="/asset-img/soft-bg.jpg" class="card-img-top" alt="rusak">
                    <div class="card-body d-flex flex-column">
                      <h3 class="card-title">{{ $pricing->pricing_name }}</h3>
                      <p class="card-text">
                        <span class="text-muted">
                            {{ strtoupper($pricing->vip_power) }} VIP Member
                        </span>
                        <hr>
                        <span>{{ $pricing->description }}</span>
                      </p>
                     <div class="bottom mt-auto text-center" style="">
                        <hr>
                        <span class="fw-bold">{{ $pricing->duration }} Hari Berlangganan!</span>
                        <div class="pricing">
                        @php
                            $getDiscount = $pricing->price * ($pricing->discount / 100);
                            $discountPrice = $pricing->price - $getDiscount;
                        @endphp

                        <strike>
                            Rp. {{ number_format($pricing->price) }}
                        </strike> <br>
                        <span class="fw-bold" style="padding:5px;">
                            Rp. {{ number_format($discountPrice) }}
                        </span>
                        
                        <form action="/transaction-view/{{ $pricing->pricing_name }}" method="GET" class="mt-4">
                            <x-bootsrap.payment-button type="submit">
                                PURCHASE
                            </x-bootsrap.payment-button>
                        </form>
                        </div>
                     </div>
                    </div>
                  </div>
                  @endforeach
                </div>
               
            </div>
        </div>
    </div>

</x-bootsrap.main-view>
