<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreUserRequest extends FormRequest
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
            'name' => 'required|string|min:3|max:100',
            'last_name' => 'required|string|min:3|max:100',
            'email' => 'required|email|max:255',
            'password' => 'required|string|min:8|max:100',
            'id_card' => 'required|integer',
            'rhgb' => 'nullable|string|size:2',
            'social_security' => 'nullable|string|max:100',
            'phone_number' => 'nullable|string|size:10',
            'is_active' => 'missing',
            'photo' => 'nullable|mimetypes:image/heic,image/heif,image/jpg,image/jpeg,image/png,image/webp,image/svg|max:2000',
            'institution_id' => 'required|integer|exists:institutions,id',
            'code' => 'required|string|max:100',
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'email.regex' => __('messages.email_domain'),
            'photo.mimetypes' => __('messages.photo_mimetypes'),
        ];
    }
}
