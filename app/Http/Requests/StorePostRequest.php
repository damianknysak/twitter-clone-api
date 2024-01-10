<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePostRequest extends FormRequest
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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => ['required'],
            'slug' => ['required'],
            'image' => 'sometimes|image|mimes:jpeg,jpg,png,gif',
            'tags' => 'sometimes|array', // Ensure 'tags' is an array
            'tags.*' => 'sometimes|string', // Ensure each element in the 'tags' array is a string

        ];
    }
}
