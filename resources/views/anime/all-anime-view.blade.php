<x-bootsrap.main-view title="Anime List">
   <x-bootsrap.sidebar-admin >

         <div class="col-sm-8 m-auto">
           <div class="konten" style="margin-top:50px;">

            <div class="navigasi col-sm-6 mb-4 d-flex justify-content-around">
               <form action="{{ route('anime-name.create') }}" method="GET">
                  <x-bootsrap.main-button type="sumbit">
                     Buat Anime
                  </x-bootsrap.main-button>
               </form>

               <form action="{{ route('anime-name.create-zip') }}" method="GET">
                  <x-bootsrap.main-button type="sumbit">
                     Buat Anime Zip
                  </x-bootsrap.main-button>
               </form>

               <form action="{{ route('anime-name.index') }}" method="GET" id="order-form">
                  <select name="order_anime_name" id="select-order" class="form-select">
                     <option value="default" 
                     {{ request('order_anime_name') != 'vip'|| request('order_anime_name') != 'non-vip' ? 'selected' : '' }}
                     >Default</option>
                     <option value="vip" {{ request('order_anime_name') == 'vip' ? 'selected' : '' }}>Vip</option>
                     <option value="non-vip" {{ request('order_anime_name') == 'non-vip' ? 'selected' : '' }}>Non Vip</option>
                  </select>
               </form>
            </div>

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
                 @foreach ($anime_name as $anime)
                  <tr>
                     <td >{{ $loop->iteration }}</td>
                     <td >{{ $anime->anime_name }}</td>
                     <td>{{ $anime->total_video }} Video</td>
                     <td>
                        <span class="fw-bold">
                           {{ $anime->vip == true ? 'YA' : 'TIDAK' }}
                        </span>
                     </td>
                     <td class="d-flex">
                        <button data-bs-toggle="modal" data-bs-target="#anime-name{{ $anime->id }}" class="btn btn-info me-2" type="button" style="border: 1px solid black;">
                           &#128065
                        </button>

                        <a class="btn btn-warning me-2" style="border: 1px solid black;" href="/anime-name/{{ $anime->slug }}/edit">&#9998</a>

                        <form action="/anime-name/{{ $anime->slug }}" method="POST">
                           @csrf
                           @method('delete')
                           <button class="btn btn-danger" style="border: 1px solid black;">
                              &#x1F5D1
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
               </tbody>
             </table>
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
