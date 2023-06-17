<x-bootsrap.guest-main-view title="Login">
    <!-- Session Status -->
   {{--  <x-auth-session-status class="mb-4" :status="session('status')" /> --}}

    

        <div class="container">
            <div class="row">
                <x-bootsrap.guest-header>
                    <h5 class="me-3 mt-2" style="opacity:50%">Belum punya akun?</h5>
                    <x-bootsrap.url-href href="{{ route('register') }}">
                       Sign Up
                     </x-bootsrap.url-href>
                </x-bootsrap.guest-header>

                <div class="d-flex vh-100 justify-content-center align-items-center">
                    <div class="card w-25">
                        <div class="card-body">
                            <form method="POST" action="{{ route('login') }}">
                                @csrf
                            <h1 class="text-center mb-4">Log In</h1>

                            <div class="email mb-3">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" name="email" class="form-control" placeholder="kawai@mail" required>
                            </div>
                            <div class="password mb-3">
                                <label for="password" class="form-label">Password</label>
                                <input type="password" name="password" class="form-control" required>
                            </div>
                        <x-bootsrap.main-button class="w-100 mb-3" type="submit">
                            Log In
                         </x-bootsrap.main-button>
                       
                         <a href="{{ route('password.request') }}" class="d-flex justify-content-center">Lupa password ?</a>
                        </form>
                      
                        </div>
                    </div>
                </div>
               
            </div>
        </div>


  
</x-bootsrap.guest-main-view>
