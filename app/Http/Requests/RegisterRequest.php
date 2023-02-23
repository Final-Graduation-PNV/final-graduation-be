<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the sign up request.
     *
     * @return array<string, mixed>
     */
    public function rules():array
    {
        return [
            'name'     => 'required|unique:users,name',
            'email'    => 'required|email|unique:users,email',
            'password' => [
                'required',
                'confirmed',
                'string',
                'min:8',              // must be at least 8 characters in length
                'regex:/[a-z]/',      // must contain at least one lowercase letter
                'regex:/[A-Z]/',      // must contain at least one uppercase letter
                'regex:/[0-9]/',      // must contain at least one digit
                'regex:/[@$!%*#?&]/', // must contain a special character
            ],
            'address' => 'required|string',
            'city'    => 'required'
        ];
    }
}
