<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class UpdateUserRequest extends FormRequest
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
            'name' => 'nullable|string|min:3|max:100',
            'last_name' => 'nullable|string|min:3|max:100',
            'email' => 'nullable|email|max:255',
            'password' => 'nullable|string|min:8|max:100',
            'id_card' => [
                Rule::unique('users', 'id_card')->ignore($this->user->id ?? Auth::id()),
                'nullable',
                'integer',
            ],
            'rhgb' => 'nullable|string|size:2',
            'social_security' => 'nullable|string|max:100',
            'phone_number' => 'nullable|string|size:10',
            'is_active' => 'nullable|boolean',
            'photo' => 'nullable|mimetypes:image/heic,image/heif,image/jpg,image/jpeg,image/png,image/webp,image/svg|max:10240',
            'code' => 'nullable|string|max:100',
            'institution_id' => 'required_with:code|integer|exists:institutions,id',
            'secondary_emails' => 'nullable|array|distinct',
            'secondary_emails.*' => 'email|max:255',
        ];
    }
}
