<x-bootsrap.main-view title="Trashed Anime List">
    <x-bootsrap.sidebar-admin >
 
          <div class="col-sm-8 m-auto">
            <div class="konten" style="margin-top:50px;">
 
             <div class="navigasi col-sm-4 mb-4 d-flex justify-content-around">
                <form action="{{ route('anime-name.index') }}" method="GET">
                   <x-bootsrap.main-button type="sumbit">
                      Published
                   </x-bootsrap.main-button>
                </form>
                <form action="{{ route('trashed-anime') }}" method="GET" id="order-form">
                   <select name="order_anime_name" id="select-order" class="form-select">
                      <option value="default" 
                      {{ request('order_anime_name') != 'vip'|| request('order_anime_name') != 'non-vip' ? 'selected' : '' }}
                      >Default</option>
                      <option value="vip" {{ request('order_anime_name') == 'vip' ? 'selected' : '' }}>Vip</option>
                      <option value="non-vip" {{ request('order_anime_name') == 'non-vip' ? 'selected' : '' }}>Non Vip</option>
                   </select>
                </form>
             </div>

           @if (session('info'))
               <h5 class="fw-bold text-center" style="color:red">{{ session('info') }}</h5>
           @endif
 
             <table class="table">
                <thead>
                  <tr>
                    <th scope="col">No</th>
                    <th scope="col">Anime</th>
                    <th scope="col">Total Video</th>
                    <th scope="col">Vip</th>
                    <th scope="col" class="">Aksi</th>
                  </tr>
                </thead>
                <tbody>
                  @if ($anime_name->count())
                  @foreach ($anime_name as $anime)
                  <tr>
                     <td >{{ $loop->iteration }}</td>
                     <td >{{ $anime->anime_name }}</td>
                     <td>{{ $anime->anime_video->count() }} Video</td>
                     <td>
                        <span class="fw-bold">
                           {{ $anime->vip == true ? 'YA' : 'TIDAK' }}
                        </span>
                     </td>
                     <td class="d-flex">
                        <button data-bs-toggle="modal" data-bs-target="#anime-name{{ $anime->id }}" class="btn btn-info me-2" type="button" style="border: 1px solid black;">
                           &#128065
                        </button>
 
                        <form action="/anime-restore/{{ $anime->slug }}" method="POST">
                            @csrf
                            <button class="btn btn-primary me-2" style="border: 1px solid black;">
                                &#9741
                            </button>
                            </form>
 
                            <form action="/anime-force-delete/{{ $anime->slug }}" method="POST">
                                @csrf
                                <button class="btn btn-danger" style="border: 1px solid black;">
                                    &#9851
                                    </button>
                                </form>
                     </td>
                  </tr>
 
                  <!-- Modal -->
                  <div class="modal fade" id="anime-name{{ $anime->id }}" tabindex="-1" aria-hidden="true">
                     <div class="modal-dialog">
                     <div class="modal-content">
                        <div class="modal-header">
                           <h1 class="modal-title fs-5" id="exampleModalLabel">Preview Anime</h1>
                           <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                         Anime Name : <span class="fw-bold mb-1">{{ $anime->anime_name }}</span> <br>
                         Slug : <span class="fw-bold mb-1">{{ $anime->slug }}</span> <br>
                         Total Eps : <span class="fw-bold mb-1">{{ $anime->total_episode }}</span> <br>
                         Studio : <span class="fw-bold mb-1">{{ $anime->studio }}</span> <br>
                         Author : <span class="fw-bold mb-1">{{ $anime->author }}</span> <br>
                         Released Date : <span class="fw-bold mb-1">{{ $anime->released_date }}</span> <br>
                         Vip : <span class="fw-bold mb-1">{{ $anime->vip == true ? 'YA' : 'TIDAK' }}</span> <br>
                         Genre : <span class="fw-bold mb-1">{{ $anime->genres->implode('genre_name' , ',') }}</span> <br>
                         Description : <br>
                         <div class="card mt-2">
                           <div class="card-body">
                              <p class="" style="font-size:15px;">
                                 {{ $anime->description }}
                              </p>
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
              <div class="paginator m-auto">
                {{ $anime_name->links() }}
              </div>
            </div>
          </div>
 
          <script>
             $(document).ready(function(){
                $('#select-order').on('change' , function(){
                   $('#order-form').submit()
                })
             })
          </script>
 
    </x-bootsrap.sidebar-admin>
 </x-bootsrap.main-view>
 