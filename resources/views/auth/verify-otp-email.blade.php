<x-guest-layout>
    <div class="mb-4 text-sm text-gray-600">
        {{ __('Thanks for signing up! Before getting started, could you verify your email address by clicking on the link we just emailed to you? If you didn\'t receive the email, we will gladly send you another.') }}
    </div>

    @if (session('status') == 'resend-otp')
        <div class="mb-4 font-medium text-sm text-green-600">
            {{ __('A new verification link has been sent to the email address you provided during registration.') }}
        </div>
    @endif

        <form action="/send-otp" method="POST">
            @csrf
            <div>
                <x-input-label for="code_otp" :value="__('Kode OTP')" />
                <x-text-input id="code_otp" name="code_otp" type="text" class="mt-1 block w-full" autocomplete="code_otp" />
            </div>

            
            <div class="mt-4">
                <x-primary-button>
                    {{ __('Kirim Kode') }}
                </x-primary-button>
            </div>
        </form>

        <form action="/resend-otp-email" method="POST">
            @csrf
          
            <div class="mt-4">
                <x-primary-button>
                    {{ __('Resend Kode') }}
                </x-primary-button>
            </div>
        </form>

        <form method="POST" action="{{ route('logout') }}">
            @csrf

            <button type="submit" class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                {{ __('Log Out') }}
            </button>
        </form>
    </div>
</x-guest-layout>
