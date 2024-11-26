<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProtocolRequest extends FormRequest
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
            'name' => [
                'nullable',
                'string',
                'min:3',
                'max:100',
                Rule::unique('protocols', 'name')->where('risk_situation_id', $this->risk_situation->id)->ignore($this->protocol->id),
            ],
            'content' => 'nullable|string',
            'risk_situation_id' => 'nullable|integer|exists:risk_situations,id',
        ];
    }
}
