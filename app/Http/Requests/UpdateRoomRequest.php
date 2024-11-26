<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateRoomRequest extends FormRequest
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
                Rule::unique('zones', 'name')->where('zone_id', $this->zone->id)->ignore($this->room->id),
            ],
            'description' => 'nullable|string',
            'code' => 'nullable|string',
            'level_id' => 'nullable|integer|exists:levels,id',
            'zone_id' => 'nullable|integer|exists:zones,id',
        ];
    }
}
