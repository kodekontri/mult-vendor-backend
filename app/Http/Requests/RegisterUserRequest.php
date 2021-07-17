<?php

namespace App\Http\Requests;

use Anik\Form\FormRequest;

class RegisterUserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    protected function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    protected function rules(): array
    {
        return [
            'username' => 'required|unique:users,username',
            'phone' => 'required|unique:users,phone',
            'email' => 'required|email:rfc|unique:users,email',
            'password' => 'required|min:6|max:18'
        ];
    }

    public function messages(): array
    {
        return [
            'username.required' => 'Provide a username',
            'username.unique' => 'Username not available',
            'phone.required' => 'Provide a phone',
            'phone.unique' => 'Phone already used',
            'email.required' => 'Provide a valid email address',
            'email.unique' => 'Email address already used',
        ];
    }
}
