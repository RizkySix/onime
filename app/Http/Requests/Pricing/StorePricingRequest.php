<?php

namespace App\Http\Requests\Pricing;

use Illuminate\Foundation\Http\FormRequest;

class StorePricingRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'pricing_name' => 'required|string|min:2|unique:pricings',
            'price' => 'required|numeric',
            'discount' => 'nullable|integer',
            'duration' => 'required|integer',
            'description' => 'required|string|min:10'
        ];
    }
}
