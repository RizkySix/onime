<x-bootsrap.guest-main-view title="Login">
    <!-- Session Status -->
   {{--  <x-auth-session-status class="mb-4" :status="session('status')" /> --}}

    

        <div class="container">
            <div class="row">
                <x-bootsrap.guest-header>
                    <h5 class="me-3 mt-2" style="opacity:50%">Sudah punya akun?</h5>
                    <x-bootsrap.url-href href="{{ route('login') }}">
                       Log in
                     </x-bootsrap.url-href>
                </x-bootsrap.guest-header>

                <div class="d-flex vh-100 justify-content-center align-items-center">
                    <div class="card w-25">
                        <div class="card-body">
                            <form method="POST" action="{{ route('register') }}">
                                @csrf
                            <h1 class="text-center mb-4">Register</h1>

                            <div class="name mb-3">
                                <label for="name" class="form-label">Your Name</label>
                                <input type="text" name="name" class="form-control" placeholder="Dek Suka" required>
                            </div>

                            <div class="email mb-3">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" name="email" class="form-control" placeholder="kawai@mail" required>
                            </div>
                            <div class="password mb-3">
                                <label for="password" class="form-label">Password</label>
                                <input type="password" name="password" class="form-control" required>
                            </div>

                            <div class="password-confirmation mb-3">
                                <label for="password-confirmation" class="form-label">Confirmation Password</label>
                                <input type="password" name="password-confirmation" class="form-control" required>
                            </div>
                        <x-bootsrap.main-button class="w-100 mb-3" type="submit">
                            Register
                         </x-bootsrap.main-button>
                       
                        </form>
                      
                        </div>
                    </div>
                </div>
               
            </div>
        </div>


  
</x-bootsrap.guest-main-view>
