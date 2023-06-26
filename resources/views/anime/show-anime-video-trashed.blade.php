<x-bootsrap.main-view title="Trashed Anime Video">
    <style>
  .error-msg{
    font-size: 15px;
  }
</style>
    <x-bootsrap.sidebar-admin >
 
          <div class="col-sm-8 m-auto">
            <div class="konten" style="margin-top:50px">

                <form action="{{ route('show-anime-video' , $anime_name->slug) }}" method="GET">
                    <x-bootsrap.main-button type="submit">
                        PUBLISHED VIDEO
                    </x-bootsrap.main-button >
                </form>
                <hr>

                <h4 class="text-center fw-bold">Trashed Video List {{ $anime_name->anime_name }}</h4>
                @if (session('info'))
                    <h5 class="fw-bold text-center" style="color:red;">{{ session('info') }}</h5>
                <br>
            @endif
              <div class="video mt-4 d-flex col-sm-12 justify-content-between flex-wrap">
               @if ($anime_name->anime_video->count())
               @foreach ($anime_name->anime_video as $anime_video)
               <div class="list d-flex col-sm-5 mb-3">
                   <form action="/anime-videos-restore/{{ $anime_video->id }}" method="POST" class="d-flex">
                       @csrf
                       <input type="text" name="anime_eps" value="{{ $anime_video->anime_eps }}" required readonly class="form-control me-2">
                       @error('anime_eps')
                      <span class="error-msg">
                       {{ $message }}
                      </span>
                       @enderror
                       <button class="btn btn-primary me-2" style="border: 1px solid black;">
                        &#9741
                        </button>
                   </form>

                   <form action="/anime-videos-force-delete/{{ $anime_video->id }}" method="POST">
                       @csrf
                       <button class="btn btn-danger" style="border: 1px solid black;">
                        &#9851
                        </button>
                   </form>
               </div>
              
           @endforeach
               @else
               <h3 class="text-center m-auto">NULL</h3>
               @endif
              </div>
            </div>
          </div>
 
    </x-bootsrap.sidebar-admin>
 </x-bootsrap.main-view>
 