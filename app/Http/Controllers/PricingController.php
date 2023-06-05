<?php

namespace App\Http\Controllers;

use App\Http\Requests\Pricing\StorePricingRequest;
use App\Http\Requests\Pricing\UpdatePricingRequest;
use App\Models\Pricing;
use Illuminate\Http\Request;

class PricingController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    { 
        $pricings = Pricing::withTrashed()->select('pricing_name' , 'price' , 'discount' , 'duration' , 'description' , 'deleted_at')->get();
        return view('pricing.all-pricing' , [
            'pricings' => $pricings,
            'user_order' => auth()->user()->order
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('pricing.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StorePricingRequest $request)
    {
        $validatedData = $request->validated();

        Pricing::create($validatedData);

        return back()->with('success' , 'New Pricing Added');
    }

    /**
     * Display the specified resource.
     */
    public function show(Pricing $pricing)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Pricing $pricing)
    {
        return view('pricing.update' , [
            'pricing' => $pricing
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdatePricingRequest $request, Pricing $pricing)
    {
        $validatedData = $request->validated();

        Pricing::where('id' , $pricing->id)->update($validatedData);

        return back()->with('success' , 'Pricing Updated');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Pricing $pricing)
    {
        Pricing::destroy($pricing->id);

        return back()->with('success' , 'Pricing Deleted');
    }

     /**
     * Restore the specified resource from storage.
     */
    public function restore($pricing_name)
    {
        $softDeleted = Pricing::onlyTrashed()->where('pricing_name' , $pricing_name)->first();

        if($softDeleted){
            $softDeleted->restore();
        }

        return back();
    }

     /**
     * Force Delete the specified resource from storage.
     */
    public function force_delete($pricing_name)
    {
        $forceDelete = Pricing::onlyTrashed()->where('pricing_name' , $pricing_name)->first();

        if($forceDelete){
            $forceDelete->forceDelete();
        }

        return back();
    }
}
