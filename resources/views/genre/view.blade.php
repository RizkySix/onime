<x-guest-layout>
 @foreach ($genres as $genre)
     <form action="/genre/{{ $genre->genre_name }}" method="POST">
        @csrf
        @method('put')
        <input type="text" name="genre_name" required value="{{ $genre->genre_name }}"><br>
        <label for="">Anime Name</label><br>
        @foreach ($genre->anime_name as $anime)
            {{ $anime->anime_name }},
        @endforeach <br>
        @error('genre_name')
            {{ $message }}
        @enderror
        @if (session('found-genre'))
            {{ session('found-genre') }}
        @endif
        <x-primary-button class="mt-4">
            {{ __('Edit') }}
        </x-primary-button>
    </form>
 @endforeach
 </x-guest-layout>