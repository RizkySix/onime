<x-bootsrap.main-view title="Trashed Genre List">
    <x-bootsrap.sidebar-admin >
 
          <div class="col-sm-8 m-auto">
            <div class="konten" style="margin-top:50px;">
 
             <div class="navigasi col-sm-5 mb-4 d-flex justify-content-around">
                <form action="{{ route('genre.index') }}" method="GET">
                   <x-bootsrap.main-button type="sumbit">
                    Published Genre
                   </x-bootsrap.main-button>
                </form>
 
 
                <form action="{{ route('genre-trashed') }}" method="GET" id="genre-anime-form">
                   <select name="order_related_anime" id="select-order" class="form-select">
                      <option value="default" 
                      {{ request('order_related_anime') != 'related-desc'|| request('order_related_anime') != 'related-asc' ? 'selected' : '' }}
                      >Default</option>
                      <option value="related-desc" {{ request('order_related_anime') == 'related-desc' ? 'selected' : '' }}>Related Video &#8593</option>
                      <option value="related-asc" {{ request('order_related_anime') == 'related-asc' ? 'selected' : '' }}>Related Video &#8595</option>
                   </select>
                </form>
 
             </div>
             @if (session('found-genre'))
             <h5 class="fw-bold text-center" style="color:red">{{ session('found-genre') }}</h5>
             @endif
             @error('genre_name')
             <h5 class="fw-bold text-center" style="color:red">{{ $message }}</h5>
             @enderror
 
             <table class="table">
                <thead>
                  <tr>
                    <th scope="col">No</th>
                    <th scope="col">Genre Name</th>
                    <th scope="col">Related Anime</th>
                    <th scope="col" class="">Aksi</th>
                  </tr>
                </thead>
                <tbody>
                  @if ($genres->count())
                  @foreach ($genres as $genre)
                  <tr>
                     <td >{{ $loop->iteration }}</td>
                       
                            <td ><input type="text" name="genre_name" class="form-control" required placeholder="Genre Name" value="{{ $genre->genre_name }}" style="width: 100px;"></td>
                            <td>{{ $genre->related_anime }} Anime</td>
                            <td class="d-flex">
                               <button data-bs-toggle="modal" data-bs-target="#anime-name{{ $genre->id }}" class="btn btn-info me-2" type="button" style="border: 1px solid black;">
                                  &#128065
                               </button>

                            <form action="/genre-restore/{{ $genre->genre_name }}" method="POST">
                                @csrf
                                <button class="btn btn-primary me-2" style="border: 1px solid black;">
                                    &#9741
                                </button>
                           
                            </form>
 
                     
 
                        <form action="/genre-force-delete/{{ $genre->genre_name }}" method="POST">
                           @csrf
                           <button class="btn btn-danger" style="border: 1px solid black;">
                            &#9851
                           </button>
                           </form>
                     </td>
                  </tr>
 
                  <!-- Modal -->
                  <div class="modal fade" id="anime-name{{ $genre->id }}" tabindex="-1" aria-hidden="true">
                     <div class="modal-dialog">
                     <div class="modal-content">
                        <div class="modal-header">
                           <h1 class="modal-title fs-5" id="exampleModalLabel">Related Anime Genre {{ $genre->genre_name }}</h1>
                           <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                         
                          @foreach ($genre->anime_name as $anime)
                          <a href="{{ route('show-anime-video' , $anime->slug) }}" class="text-decoration-none">
                            <div class="card mb-2">
                                <div class="card-body" style="color: rgb(72, 72, 190)">
                                    {{ $anime->anime_name }}
                                </div>
                            </div>
                        </a>
                          @endforeach
                           
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
                {{ $genres->links() }}
              </div>
 
            </div>
          </div>
 
          <script>
             $(document).ready(function(){
                $('#select-order').on('change' , function(){
                   $('#genre-anime-form').submit()
                })
             })
          </script>
 
    </x-bootsrap.sidebar-admin>
 </x-bootsrap.main-view>
 