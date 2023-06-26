<x-bootsrap.main-view title="Create Anime With Zip">
<style>
  .error-msg{
    font-size: 15px;
  }
</style>

  <x-bootsrap.sidebar-admin >

        <div class="col-sm-8 m-auto">
          <div class="konten" style="margin-top:50px;">
              
              <h5 class="fw-bold text-center">Publish Anime Baru Zip</h5>
                  @if (session('info'))
                  <h5 class="fw-bold text-center" style="color:red">
                      {{ session('info') }}
                  </h5>
                  @endif
  

              <form action="{{ route('anime-name.store.zip') }}" method="POST" enctype="multipart/form-data">
                  @csrf
                  <div class="col-sm-12 mt-4">
                      <div class="first-input d-flex justify-content-around">
                          <div class="col-sm-5 ">
                              <label for="anime_name" class="form-label">Anime Name</label>
                              <input type="text" name="anime_name" id="anime_name" placeholder="Anime Name Goes Here" class="form-control" required value="{{ old('anime_name') }}">
                              @error('anime_name')
                                  <span style="color:red" class="error-msg">{{ $message }}</span>
                              @enderror
                          </div>
                         <div class="col-sm-5">
                          <label for="released_date" class="form-label">Released Date</label>
                          <input type="text" name="released_date" id="released_date" placeholder="Released Date Goes Here" class="form-control" value="{{ old('released_date') }}">
                          @error('released_date')
                          <span style="color:red" class="error-msg">{{ $message }}</span>
                      @enderror
                         </div>
                      </div>
                      <div class="second-input d-flex mt-4 justify-content-around">
                          <div class="col-sm-2 ">
                              <label for="total_episode" class="form-label">Total Eps</label>
                              <input type="number" name="total_episode" id="total_episode" placeholder="Total Eps Goes Here" class="form-control" required value="{{ old('total_episode') }}">
                              @error('total_episode')
                              <span style="color:red" class="error-msg">{{ $message }}</span>
                           @enderror
                          </div>
                          <div class="col-sm-2 ">
                              <label for="studio" class="form-label">Studio</label>
                              <input type="text" name="studio" id="studio" placeholder="Studio Goes Here" class="form-control" required value="{{ old('studio') }}">
                              @error('studio')
                              <span style="color:red" class="error-msg">{{ $message }}</span>
                          @enderror
                          </div>
                          <div class="col-sm-2">
                              <label for="author" class="form-label">Author</label>
                              <input type="text" name="author" id="author" placeholder="Author Goes Here" class="form-control" required value="{{ old('author') }}">
                              @error('author')
                              <span style="color:red" class="error-msg">{{ $message }}</span>
                          @enderror
                          </div>
                          <div class="col-sm-3">
                              <label for="genre" class="form-label">Genres</label>
                              <input type="text" name="genre" id="genre" placeholder="Shounen,Demon,Action" class="form-control" required value="{{ old('genre') }}">
                              @error('genre')
                              <span style="color:red" class="error-msg">{{ $message }}</span>
                          @enderror
                             </div>
                      </div>
                      <div class="second-input d-flex mt-4 justify-content-around">
                          <div class="col-sm-12 ">
                              <label for="description" class="form-label text-center">Description</label>
                              <textarea class="form-control" name="description" id="description" cols="30" rows="10" required>{{ old('description') }}</textarea>
                              @error('description')
                              <span style="color:red" class="error-msg">{{ $message }}</span>
                          @enderror
                          </div>
                      </div>
                      <div class="vip">
                          <input type="checkbox" name="vip" value="1" class="form-check-input"> <span class="text-muted">Checklist jika anime ini VIP</span>
                      </div>
                      <div class="zip mt-2 col-sm-6">
                        <label for="zip" class="form-label text-center">Zip File (Maks: 1 GB)</label>
                        <input type="file" name="zip" class="form-control" id="zip" required>
                        @error('zip')
                          <span style="color:red" class="error-msg">{{ $message }}</span>
                      @enderror
                    </div>
                  </div>
                  <x-bootsrap.main-button type="submit" class="mt-3 mb-4">
                      PUBLISH ANIME
                  </x-bootsrap.main-button>
              </form>

           
          </div>
        </div>

  </x-bootsrap.sidebar-admin>
</x-bootsrap.main-view>
