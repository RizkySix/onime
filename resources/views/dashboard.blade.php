<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    {{ __("You're logged in!") }} 
                    <form action="{{ route('token-maker') }}" method="POST">
                    @csrf
                    <input type="text" readonly disabled value="{{ auth()->user()->token }}" style="width:500px;">
                    <button>Buat Token</button>
                    </form>
                    <br><br>
                    <div class="mt-4">
                        <form action="/anime-name" method="POST" enctype="multipart/form-data">
                        @csrf
                        <label for="">Kirim Video</label> <br>
                        <input type="text" name="anime_name" placeholder="anime name">
                        <input type="text" name="total_episode" placeholder="total eps">
                        <input type="text" name="studio" placeholder="studio">
                        <input type="text" name="author" placeholder="author"><br><br>
                        <textarea name="description" id="" cols="30" rows="10"></textarea> <br>
                        <input type="file" name="video"> <br><br>
                        <x-primary-button>
                            {{ __('Kirim') }}
                        </x-primary-button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
