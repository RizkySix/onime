<x-guest-layout>
    <form action="/anime-name/{{ $anime_name->slug }}" method="POST">
        @csrf
        @method('put')
        <input type="text" name="anime_name" value="{{ $anime_name->anime_name }}"><br>
        @error('anime_name')
          {{ $message }}
        @enderror
        <input type="text" name="total_episode" value="{{ $anime_name->total_episode }}"><br>
        @error('total_episode')
        {{ 'error' }}
    @enderror
        <input type="text" name="studio" value="{{ $anime_name->studio }}"><br>
        <input type="text" name="author" value="{{ $anime_name->author }}"><br>
        <input type="text" name="description" value="{{ $anime_name->description }}"><br>

        <x-primary-button class="mt-4">
            {{ __('Edit') }}
        </x-primary-button>
    </form> <br><br>

    @foreach ($anime_name->anime_video as $item)
        <form action="/anime-videos/{{ $item->id }}" method="POST">
            @csrf
            @method('put')
         
            <input type="text" name="anime_eps" value="{{ $item->anime_eps }}" required style="width:360px;">
            @if (session('duplicate-found'))
                {{ session('duplicate-found') }}
                <br>
            @endif
            <x-primary-button class="mt-2">
                {{ __('Perbarui') }}
            </x-primary-button>

        </form> <br>
    @endforeach
 </x-guest-layout>