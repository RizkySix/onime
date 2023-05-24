<x-guest-layout>
    <div class="mt-4">
        <form action="{{ route('anime-videos.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <input type="text" readonly name="anime_name_slug" value="{{ request('anime-name') }}">
            <input type="file" class="" name="video">
            @if (session('no-match'))
                {{ session('no-match') }}
            @endif
       
        <x-primary-button>
            {{ __('Kirim Kode') }}
        </x-primary-button>
    </form>
    </div>
</x-guest-layout>
