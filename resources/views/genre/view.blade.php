<x-guest-layout>
 @foreach ($genres as $genre)
    @if ($genre->trashed())
    <form action="/genre-restore/{{ $genre->genre_name }}" method="POST">
        @csrf
        <input type="text" name="genre_name" required value="{{ $genre->genre_name }}"><br>
        <label for="">Anime Name</label><br>
        @foreach ($genre->anime_name as $anime)
            {{ $anime->anime_name }},
        @endforeach <br>
        <x-primary-button class="mb-2">
            {{ __('Restore') }}
        </x-primary-button>
    </form>

    <form action="/genre-force-delete/{{ $genre->genre_name }}" method="POST">
        @csrf
        <x-primary-button class="mt-2 mb-4">
            {{ __('Force delete') }}
        </x-primary-button>
    </form>
    @else
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
        <x-primary-button class="mb-2">
            {{ __('Edit') }}
        </x-primary-button>
    </form>

    <form action="/genre/{{ $genre->genre_name }}" method="POST">
        @csrf
        @method('delete')
        <x-primary-button class="mt-2 mb-4">
            {{ __('Delete') }}
        </x-primary-button>
    </form>

    @endif
    <hr>
 @endforeach
 </x-guest-layout>