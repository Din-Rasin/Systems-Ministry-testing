<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class UserRequest extends FormRequest
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
        $rules = [
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users',
            'password' => ['required', 'string', Password::min(8)->mixedCase()->numbers()->symbols()],
            'department_id' => 'required|exists:departments,id',
            'roles' => 'required|array|min:1',
            'roles.*' => 'exists:roles,id',
        ];

        // If this is an update request, modify the rules
        if (in_array($this->method(), ['PATCH', 'PUT'])) {
            $userId = $this->route('user') ? $this->route('user')->id : $this->user()->id;
            $rules['email'] = 'required|email|max:255|unique:users,email,' . $userId;
            $rules['password'] = ['nullable', 'string', Password::min(8)->mixedCase()->numbers()->symbols()];
        }

        return $rules;
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Name is required.',
            'name.max' => 'Name must not exceed 255 characters.',
            'email.required' => 'Email is required.',
            'email.email' => 'Email must be a valid email address.',
            'email.max' => 'Email must not exceed 255 characters.',
            'email.unique' => 'This email is already registered.',
            'password.required' => 'Password is required.',
            'password.min' => 'Password must be at least 8 characters.',
            'department_id.required' => 'Department is required.',
            'department_id.exists' => 'Selected department is invalid.',
            'roles.required' => 'At least one role is required.',
            'roles.array' => 'Roles must be an array.',
            'roles.min' => 'At least one role is required.',
            'roles.*.exists' => 'Selected role is invalid.',
        ];
    }
}
