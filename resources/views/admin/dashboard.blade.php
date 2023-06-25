<x-bootsrap.main-view title="Dashboard Admin">
    <x-bootsrap.sidebar-admin >

    <div class="col-sm-8 m-auto" >
       <div class="d-flex justify-content-around" style="margin-top:50px; margin-bottom:50px;">
        @foreach ($pricings as $pricing)
        <div class="card m-auto" style="width:24rem">
            <div class="card-body text-center">
              <h4 class="fw-bold">
                {{ $pricing->pricing_name }} Member
              </h4>
              <hr>
              <span class="text-muted">Total User Berlangganan Saat Ini</span><br>
              <span class="fw-bold">{{ $pricing->vip_total }}</span>
            </div>
        </div>
        @endforeach
       </div>

       <div class="card m-auto w-100">
        <div class="card-body text-center d-flex">
          <div class="pesanan-dibuat me-auto">
            <h5 class="fw-bold">
                Total Pesanan Dibuat
            </h5>
            <hr>
            <span class="text-muted">Total Pesanan Dibuat Bulan Ini</span><br>
            <span class="fw-bold">{{ $orders }}</span>
          </div>

          <div class="pesanan-dibayar ms-auto">
            <h5 class="fw-bold">
                Total Pesanan Dibayar
            </h5>
            <hr>
            <span class="text-muted">Total Pesanan Terbayar Bulan Ini</span><br>
            <span class="fw-bold">{{ $paid_orders }}</span>
            
          </div>
        </div>
    </div>
    
    </div>

    </x-bootsrap.sidebar-admin>
</x-bootsrap.main-view>
