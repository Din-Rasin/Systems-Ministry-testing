<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class MissionRequestRequest extends FormRequest
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
            'destination' => 'required|string|max:255',
            'purpose' => 'required|string|max:1000',
            'start_date' => 'required|date|after_or_equal:today',
            'end_date' => 'required|date|after_or_equal:start_date',
            'budget' => 'required|numeric|min:0',
            'supporting_document' => 'nullable|file|mimes:pdf,doc,docx,jpg,png|max:2048',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'destination.required' => 'Destination is required.',
            'destination.max' => 'Destination must not exceed 255 characters.',
            'purpose.required' => 'Purpose is required.',
            'purpose.max' => 'Purpose must not exceed 1000 characters.',
            'start_date.required' => 'Start date is required.',
            'start_date.date' => 'Start date must be a valid date.',
            'start_date.after_or_equal' => 'Start date must be today or in the future.',
            'end_date.required' => 'End date is required.',
            'end_date.date' => 'End date must be a valid date.',
            'end_date.after_or_equal' => 'End date must be after or equal to start date.',
            'budget.required' => 'Budget is required.',
            'budget.numeric' => 'Budget must be a number.',
            'budget.min' => 'Budget must be zero or positive.',
            'supporting_document.file' => 'Supporting document must be a file.',
            'supporting_document.mimes' => 'Supporting document must be a PDF, DOC, DOCX, JPG, or PNG file.',
            'supporting_document.max' => 'Supporting document must not exceed 2MB.',
        ];
    }
}
