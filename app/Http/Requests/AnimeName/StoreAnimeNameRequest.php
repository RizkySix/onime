<?php

namespace App\Http\Requests\AnimeName;

use Illuminate\Foundation\Http\FormRequest;

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
            'anime_name' => 'required|string|min:3|unique:anime_names',
            'total_episode' => 'required|integer',
            'studio' => 'required|string|min:3',
            'author' => 'required|string|min:3',
            'description' => 'required|string|min:3',
            'video' => 'required|mimes:mp4,mov,avi,mkv',
        ];
    }
}
