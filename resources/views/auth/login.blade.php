<x-bootsrap.guest-main-view title="Login">
    <!-- Session Status -->
   {{--  <x-auth-session-status class="mb-4" :status="session('status')" /> --}}

    

        <div class="container">
            <div class="row">
                <div class="top-guest-component w-100 bg-info d-flex">
                    <div class="left me-auto">
                       Logo
                    </div>

                    <div class="right ms-auto">
                        Singup
                    </div>
                </div>

                <div class="d-flex vh-100 justify-content-center align-items-center">
                    <div class="card w-25">
                        <div class="card-body">
                            <form method="POST" action="{{ route('login') }}">
                                @csrf
                            <h1 class="text-center mb-4">Logo</h1>

                            <div class="email mb-3">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" name="email" class="form-control" placeholder="kawai@mail" required>
                            </div>
                            <div class="password mb-3">
                                <label for="password" class="form-label">Password</label>
                                <input type="password" name="password" class="form-control" required>
                            </div>
                        <x-bootsrap.main-button class="w-100" type="submit">
                            Log In
                         </x-bootsrap.main-button>

                        </form>
                        </div>
                    </div>
                </div>
               
            </div>
        </div>


  
</x-bootsrap.guest-main-view>
