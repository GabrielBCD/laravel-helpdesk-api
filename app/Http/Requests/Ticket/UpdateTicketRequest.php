<?php

namespace App\Http\Requests\Ticket;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTicketRequest extends FormRequest
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
            'syndic_name'      => ['sometimes', 'required', 'string', 'max:255'],
            'phone'            => ['sometimes', 'required', 'string', 'max:20'],
            'condominium_name' => ['sometimes', 'required', 'string', 'max:255'],
            'zip_code'         => ['sometimes', 'required', 'string', 'max:10'],
            'email'            => ['sometimes', 'required', 'email', 'max:255'],
            'status'           => ['sometimes', 'required', Rule::in('open', 'verifying', 'finished')],
        ];
    }
}

