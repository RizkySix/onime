<x-bootsrap.main-view title="Show Anime Video">
    <style>
  .error-msg{
    font-size: 15px;
  }
</style>
    <x-bootsrap.sidebar-admin >
 
          <div class="col-sm-8 m-auto">
            <div class="konten" style="margin-top:50px">

                <form action="{{ route('show-anime-video-trashed' , $anime_name->slug) }}" method="GET">
                    <x-bootsrap.main-button type="submit">
                        TRASHED VIDEO
                    </x-bootsrap.main-button >
                </form>
                <hr>

                <h4 class="text-center fw-bold">Video List {{ $anime_name->anime_name }}</h4>
                @if (session('info'))
                    <h5 class="fw-bold text-center" style="color:red;">{{ session('info') }}</h5>
                <br>
            @endif
              <div class="video mt-4 d-flex col-sm-12 justify-content-between flex-wrap">
               @if ($anime_name->anime_video->count())
               @foreach ($anime_name->anime_video as $anime_video)
               <div class="list d-flex col-sm-5 mb-3">
                   <form action="/anime-videos/{{ $anime_video->id }}" method="POST" class="d-flex">
                       @csrf
                       @method('put')
                       <input type="text" name="anime_eps" value="{{ $anime_video->anime_eps }}" required class="form-control me-2">
                       @error('anime_eps')
                      <span class="error-msg">
                       {{ $message }}
                      </span>
                       @enderror
                       <button class="btn btn-warning me-2" style="border: 1px solid black;">
                           &#9998
                        </button>
                   </form>

                   <form action="/anime-videos/{{ $anime_video->id }}" method="POST">
                       @csrf
                       @method('delete')
                       <button class="btn btn-danger" style="border: 1px solid black;">
                           &#x1F5D1
                        </button>
                   </form>
               </div>
              
           @endforeach
               @else
               <h3 class="m-auto">NULL</h3>
               @endif
              </div>
            </div>
          </div>
 
    </x-bootsrap.sidebar-admin>
 </x-bootsrap.main-view>
 