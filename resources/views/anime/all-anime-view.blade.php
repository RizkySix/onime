<x-guest-layout>
   @foreach ($anime_name as $anime)
   <div class="mt-4">
    <h5>{{ $anime->anime_name }}</h5>
    <br>
    <label for="">Video</label> <br>
   @foreach ($anime->anime_video as $item)
       {{ $item->video_url }} <br>
   @endforeach <br>
   <a href="/anime-name/{{ $anime->slug }}/edit"><h1>EDIT</h1></a>
</div>
   @endforeach
</x-guest-layout>