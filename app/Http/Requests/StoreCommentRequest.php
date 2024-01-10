<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCommentRequest extends FormRequest
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
            'comment' =>  [
                'required', 'string',
                'max:255'
            ],
            'postId' => [
                'required',
                Rule::exists('posts', 'id')
            ]
        ];
    }

    protected function prepareForValidation()
    {
        $this->merge([
            'post_id' => $this->postId
        ]);
    }
}
