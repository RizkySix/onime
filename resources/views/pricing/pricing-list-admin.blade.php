<x-bootsrap.main-view title="Pricing List Admin">
    <x-bootsrap.sidebar-admin >
 
          <div class="col-sm-8 m-auto">
            <div class="konten" style="margin-top:50px;">
 
             <div class="navigasi col-sm-5 mb-4 d-flex justify-content-around">
                <form action="{{ route('pricing.admin-trashed') }}" method="GET">
                   <x-bootsrap.main-button type="sumbit">
                     Trashed Pricing
                   </x-bootsrap.main-button>
                </form>

                <form action="{{ route('pricing.create') }}" method="GET">
                    <x-bootsrap.main-button type="sumbit">
                      Buat Pricing
                    </x-bootsrap.main-button>
                 </form>
 
 
              
             </div>
             @if (session('success'))
             <h5 class="fw-bold text-center" style="color:red">{{ session('success') }}</h5>
             @endif
    
 
             <table class="table">
                <thead>
                  <tr>
                    <th scope="col">No</th>
                    <th scope="col">Pricing Name</th>
                    <th scope="col">Power</th>
                    <th scope="col">Harga</th>
                    <th scope="col">Diskon</th>
                    <th scope="col">Durasi Berlangganan</th>
                    <th scope="col" class="">Aksi</th>
                  </tr>
                </thead>
                <tbody>
                  @if ($pricings->count())
                  @foreach ($pricings as $pricing)
                  <tr>
                     <td >{{ $loop->iteration }}</td>
                      
                            <td>{{ $pricing->pricing_name }}</td>
                            <td><span class="fw-bold">{{ $pricing->vip_power }} VIP</span></td>
                            <td>Rp. {{ number_format($pricing->price) }}</td>
                            <td>{{ $pricing->discount }}%</td>
                            <td>{{ $pricing->duration }} Hari</td>
                            <td class="d-flex">
                               <button data-bs-toggle="modal" data-bs-target="#anime-name{{ $pricing->id }}" class="btn btn-info me-2" type="button" style="border: 1px solid black;">
                                  &#128065
                               </button>

                               <a href="/pricing/{{ $pricing->pricing_name }}/edit" class="btn btn-warning me-2" style="border: 1px solid black;">  &#9998</a>
                      
                        <form action="/pricing/{{ $pricing->pricing_name }}" method="POST">
                           @csrf
                           @method('delete')
                           <button class="btn btn-danger" style="border: 1px solid black;">
                              &#x1F5D1
                           </button>
                           </form>
                     </td>
                  </tr>
 
                  <!-- Modal -->
                  <div class="modal fade" id="anime-name{{ $pricing->id }}" tabindex="-1" aria-hidden="true">
                     <div class="modal-dialog">
                     <div class="modal-content">
                        <div class="modal-header">
                           <h1 class="modal-title fs-5" id="exampleModalLabel">Pricing Description {{ $pricing->pricing_name }}</h1>
                           <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="card">
                                <div class="card-body">
                                    {{ $pricing->description }}
                                </div>
                            </div>
                           
                        </div>
                     </div>
                     </div>
                  </div>
                 @endforeach
                  @else
                  <td colspan="5">NULL</td>
                  @endif
                </tbody>
              </table>
 
 
            </div>
          </div>
 
    </x-bootsrap.sidebar-admin>
 </x-bootsrap.main-view>
 