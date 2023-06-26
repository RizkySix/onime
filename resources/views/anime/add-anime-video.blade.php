<x-bootsrap.main-view title="Add Anime Video">
    <style>
  .error-msg{
    font-size: 15px;
  }
</style>
    <x-bootsrap.sidebar-admin >
 
          <div class="col-sm-8 m-auto">
            <div class="konten" style="margin-top:50px;">
                
                <h5 class="fw-bold text-center">Tambah Video Anime {{ request('anime-name') }}</h5>
                    @if (session('no-match'))
                    <h5 class="fw-bold text-center" style="color:red">
                        {{ session('no-match') }}
                    </h5>
                    @endif
                    @if (session('success'))
                    <h5 class="fw-bold text-center" style="color:rgb(204, 129, 129)">
                        {{ session('success') }}
                    </h5>
                @endif
               

                    <form action="{{ route('anime-videos.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" name="anime_name_slug" class="form-control" value="{{ request('anime-slug') }}" readonly required>
                        <label for="video" class="form-label">Video File</label>
                        <input type="file" class="form-control" name="video" id="video" required>
                   
                    <x-bootsrap.main-button type="submit" class="mt-3">
                        ADD VIDEO
                     </x-bootsrap.main-button>
                </form>
 
             
            </div>
          </div>
 
    </x-bootsrap.sidebar-admin>
 </x-bootsrap.main-view>
 