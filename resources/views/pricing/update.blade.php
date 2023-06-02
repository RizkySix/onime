<x-guest-layout>
    <form action="/pricing/{{ $pricing->pricing_name }}" method="POST">
        @csrf
        @method('put')
        <input type="text" name="pricing_name" placeholder="Pricing name" value="{{ $pricing->pricing_name }}"> <br>
        @error('pricing_name')
            {{ $message }}
        @enderror
        <input type="number" name="price" placeholder="Price" value="{{ $pricing->price }}"> <br>
        <input type="number" name="discount" placeholder="discount" value="{{ $pricing->discount }}"><br>
        <input type="number" name="duration" placeholder="duration" value="{{ $pricing->duration }}"><br>
        <textarea name="description" id="" cols="30" rows="10">{{ $pricing->description }}</textarea><br><br>
        <x-primary-button>
            {{ __('Buat Pricing') }}
        </x-primary-button>
    </form>
 </x-guest-layout>