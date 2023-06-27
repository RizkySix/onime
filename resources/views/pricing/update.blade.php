<x-bootsrap.main-view title="Edit Pricing">
    <style>
  .error-msg{
    font-size: 15px;
  }
</style>
    <x-bootsrap.sidebar-admin >
 
          <div class="col-sm-8 m-auto">
            <div class="konten" style="margin-top:50px;">
                
                <h5 class="fw-bold text-center">Perbarui Pricing</h5>
                    @if (session('success'))
                    <h5 class="fw-bold text-center" style="color:rgb(204, 129, 129)">
                       {{ session('success') }}
                    </h5>
                    @endif
               

                <form action="/pricing/{{ $pricing->pricing_name }}" method="POST">
                    @csrf
                    @method('put')
                    <div class="col-sm-12 mt-4">
                        <div class="first-input d-flex justify-content-around">
                            <div class="col-sm-11 ">
                                <label for="pricing_name" class="form-label">Pricing Name</label>
                                <input type="text" name="pricing_name" id="pricing_name" placeholder="Pricing Name Goes Here" class="form-control" required value="{{ old('pricing_name' , $pricing->pricing_name) }}">
                                @error('pricing_name')
                                    <span style="color:red" class="error-msg">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                        <div class="second-input d-flex mt-4 justify-content-around">
                            <div class="col-sm-3 ">
                                <label for="price" class="form-label">Price</label>
                                <input type="number" name="price" id="price" placeholder="Price Goes Here" class="form-control" required value="{{ old('price' , $pricing->price) }}">
                                @error('price')
                                <span style="color:red" class="error-msg">{{ $message }}</span>
                             @enderror
                            </div>
                            <div class="col-sm-3 ">
                                <label for="discount" class="form-label">Discount</label>
                                <input type="number" name="discount" id="discount" placeholder="Discount Goes Here" class="form-control" value="{{ old('discount' , $pricing->discount) }}">
                                @error('discount')
                                <span style="color:red" class="error-msg">{{ $message }}</span>
                            @enderror
                            </div>
                            <div class="col-sm-3">
                                <label for="duration" class="form-label">Duration</label>
                                <input type="number" name="duration" id="duration" placeholder="Duration Goes Here" class="form-control" required value="{{ old('duration' , $pricing->duration) }}">
                                @error('duration')
                                <span style="color:red" class="error-msg">{{ $message }}</span>
                            @enderror
                            </div>
                           
                        </div>
                        <div class="second-input d-flex mt-4 justify-content-around">
                            <div class="col-sm-12 ">
                                <label for="description" class="form-label text-center">Description</label>
                                <textarea class="form-control" name="description" id="description" cols="30" rows="10" required>{{ old('description' , $pricing->description) }}</textarea>
                                @error('description')
                                <span style="color:red" class="error-msg">{{ $message }}</span>
                            @enderror
                            </div>
                        </div>

                    </div>
                    <x-bootsrap.main-button type="submit" class="mt-3">
                       PERBARUI PRICING
                    </x-bootsrap.main-button>
                </form>
 
             
            </div>
          </div>
 
    </x-bootsrap.sidebar-admin>
 </x-bootsrap.main-view>
 