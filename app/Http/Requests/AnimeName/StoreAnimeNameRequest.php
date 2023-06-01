<?php

namespace App\Http\Requests\AnimeName;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAnimeNameRequest extends FormRequest
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
            'anime_name' => 'required|string|min:3|unique:anime_names', //. Rule::unique('anime_names')->where('deleted_at' , null),
            'total_episode' => 'required|integer',
            'studio' => 'required|string|min:3',
            'author' => 'required|string|min:3',
            'description' => 'required|string|min:3',
            'released_date' => 'nullable|string|min:3',
            'genre' => 'required|string|min:3',
            'vip' => 'nullable'
         
        ];
    }
}
