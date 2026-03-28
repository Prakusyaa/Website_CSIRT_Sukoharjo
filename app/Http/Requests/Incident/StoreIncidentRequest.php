<?php

namespace App\Http\Requests\Incident;

use Illuminate\Foundation\Http\FormRequest;

class StoreIncidentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('create', \App\Models\Report::class);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'subject' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'category_id' => ['nullable', 'integer', 'exists:categories,id'],
            'severity_id' => ['nullable', 'integer', 'exists:severities,id'],
            'assigned_to' => ['nullable', 'integer', 'exists:users,id'],
            'reporter_id' => ['nullable', 'integer', 'exists:users,id'],
            'reporter_email' => ['nullable', 'email', 'max:255'],
            'attachments' => ['nullable', 'array', 'max:5'],
            'attachments.*' => ['file', 'mimes:jpg,jpeg,png,pdf,csv,txt,zip', 'max:10240'],
        ];
    }
}
