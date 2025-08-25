<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class WorkflowRequest extends FormRequest
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
            'name' => 'required|string|max:255',
            'department_id' => 'required|exists:departments,id',
            'type' => 'required|in:leave,mission',
            'description' => 'nullable|string|max:1000',
            'is_active' => 'boolean',
            'steps' => 'array',
            'steps.*.step_number' => 'required|integer|min:1',
            'steps.*.role_id' => 'required|exists:roles,id',
            'steps.*.description' => 'nullable|string|max:255',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Workflow name is required.',
            'name.max' => 'Workflow name must not exceed 255 characters.',
            'department_id.required' => 'Department is required.',
            'department_id.exists' => 'Selected department is invalid.',
            'type.required' => 'Workflow type is required.',
            'type.in' => 'Workflow type must be either leave or mission.',
            'description.max' => 'Description must not exceed 1000 characters.',
            'is_active.boolean' => 'Active status must be a boolean value.',
            'steps.array' => 'Steps must be an array.',
            'steps.*.step_number.required' => 'Step number is required.',
            'steps.*.step_number.integer' => 'Step number must be an integer.',
            'steps.*.step_number.min' => 'Step number must be at least 1.',
            'steps.*.role_id.required' => 'Role is required for each step.',
            'steps.*.role_id.exists' => 'Selected role is invalid.',
            'steps.*.description.max' => 'Step description must not exceed 255 characters.',
        ];
    }
}
