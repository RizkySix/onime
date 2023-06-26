<x-guest-layout>
    @foreach ($anime_name as $anime)
    <div class="mt-4">
     <h5>{{ $anime->anime_name }}</h5>
     <br>
     <label for="">Genre</label><br>
     @foreach ($anime->genres as $genre)
         {{ $genre->genre_name }},
     @endforeach <br>
     <label for="">Video</label> <br>
    @foreach ($anime->anime_video as $item)
        {{ $item->video_url }} <br>
    @endforeach <br>
    <a href="/anime-name/{{ $anime->slug }}/edit"><h1>EDIT</h1></a>
 
    @if ($anime->trashed())
    <form action="/anime-restore/{{ $anime->slug }}" method="POST">
       @csrf
       <x-primary-button class="mt-4">
          {{ __('Restore') }}
      </x-primary-button>
       </form>
       <form action="/anime-force-delete/{{ $anime->slug }}" method="POST">
          @csrf
          <x-primary-button class="mt-4">
             {{ __('Force Delete') }}
         </x-primary-button>
          </form>
 
    @else
    <form action="/anime-name/{{ $anime->slug }}" method="POST">
       @csrf
       @method('delete')
       <x-primary-button class="mt-4">
          {{ __('Delete') }}
      </x-primary-button>
       </form>
    @endif
 </div>
    @endforeach
 </x-guest-layout>