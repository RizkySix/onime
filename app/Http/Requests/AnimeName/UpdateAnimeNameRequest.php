<?php

namespace App\Http\Requests\AnimeName;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAnimeNameRequest extends FormRequest
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
            'anime_name' => 'required|string|min:3|unique:anime_names,anime_name,' . $this->route('anime_name')->id,
            'total_episode' => 'required|integer',
            'studio' => 'required|string|min:3',
            'author' => 'required|string|min:3',
            'description' => 'required|string|min:3',
         
        ];
    }
}
