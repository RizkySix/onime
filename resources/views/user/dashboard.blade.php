<x-bootsrap.main-view title="Dashboard">
    <x-bootsrap.navbar>
    </x-bootsrap.navbar>

    <div class="container" style="margin-top: 50px;">
        <div class="row">
            <div class="col-sm-8 m-auto">
                <div class="header">
                   @if ($auth->token)
                       <h3 class="text-center mb-4">Gunakan Token Dibawah Untuk Request API</h3>
                   @else
                   <h3 class="text-center mb-4">Silahkan Generate Token Anda Untuk Memulai</h3>
                   @endif
                </div>

                <div class="token">
                    <form action="{{ route('token-maker') }}" method="POST">
                        @csrf
                            
                        <div class="timer">
                        @if (\Carbon\Carbon::now() > \Carbon\Carbon::parse($auth->tokens[0]->created_at)->addDays(1))
                        <h6 class="text-muted ms-auto col-sm-4 mt-2">
                            Generate Token Tersedia  
                        </h6>
                        @elseif(\Carbon\Carbon::parse($auth->tokens[0]->created_at)->addDays(1)->diffInHours() >= 1)
                        <h6 class="text-muted ms-auto col-sm-4 mt-2">
                          Generate kembali dalam {{ \Carbon\Carbon::parse($auth->tokens[0]->created_at)->addDays(1)->diffInHours() }} jam
                        </h6>
                        @else
                        <h6 class="text-muted ms-auto col-sm-4 mt-2">
                        Generate kembali dalam {{ \Carbon\Carbon::parse($auth->tokens[0]->created_at)->addDays(1)->diffInMinutes() }} menit
                        </h6>
                        @endif
                          </div>
                        <div class="input-group">
                            <input type="text" class="form-control text-center" value="{{ $auth->token }}" placeholder="Generate your bearer token" readonly style="height: 70px; font-size:21px;">
                          <x-bootsrap.main-button type="submit">
                            GENERATE
                          </x-bootsrap.main-button>
                          </div>
                        @if (session('limit'))
                           <h5 class="mt-2 text-center" style="color:rgb(212, 51, 51)">{{ session('limit') }}</h5>
                        @endif
                  
                        </form>
                </div>

                <div class="vip-user col-sm-8 m-auto mt-4">
                    <div class="card">
                        <div class="card-body">
                        @if ($auth->vip->count())
                        @php
                         /*  $vips = $auth->vip->load('pricing'); */
                        @endphp
                            @foreach ($auth->vip as $vip)
                                @if ($vip->vip_duration > \Carbon\Carbon::now())
                                <div class="vip-detail">
                                   <h5 class="fw-bold text-center">{{ $vip->pricing->pricing_name }} MEMBER</h5>
                                    <div class="bg-warning text-center col-sm-4 m-auto" style="padding:5px;">
                                       <span class="text-muted"> {{ $vip->pricing->vip_power }} VIP Token</span>
                                    </div>
                                    
                                    <h6 class="text-muted text-center mt-3">
                                        Durasi berlangganan anda akan habis dalam {{ $vip->vip_duration->diffForHumans() }}
                                    </h6>
                                </div>
                                <hr>
                                @elseif($auth->vip->count() <= 1)
                                    <div class="non-vip">
                                        <h5 class="fw-bold text-center">Kamu Belum Berlangganan VIP 😒</h5> <br>
                                    </div>
                                @endif
                              
                             @endforeach
                        @else
                        <div class="non-vip">
                            <h5 class="fw-bold text-center">Kamu Belum Berlangganan VIP 😒</h5> <br>
                        </div>
                        @endif
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

</x-bootsrap.main-view>
