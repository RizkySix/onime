<x-guest-layout>
    <form action="/pricing" method="POST">
        @csrf
        <input type="text" name="pricing_name" placeholder="Pricing name"> <br>
        @error('pricing_name')
            {{ $message }}
        @enderror
        <input type="number" name="price" placeholder="Price"> <br>
        <input type="number" name="discount" placeholder="discount"><br>
        <input type="number" name="duration" placeholder="duration"><br>
        <textarea name="description" id="" cols="30" rows="10"></textarea><br><br>
        <x-primary-button>
            {{ __('Buat Pricing') }}
        </x-primary-button>
    </form>
 </x-guest-layout>