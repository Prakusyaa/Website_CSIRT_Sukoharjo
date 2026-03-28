<?php

namespace App\Http\Requests\Incident;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateIncidentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $id = $this->route('incident');
        $incident = \App\Models\Report::findOrFail($id);
        
        return $this->user()->can('update', $incident);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $id = $this->route('incident');
        $incident = \App\Models\Report::findOrFail($id);
        
        return [
            'subject' => ['sometimes', 'string', 'max:255'],
            'description' => ['sometimes', 'string'],
            'category_id' => ['nullable', 'integer', 'exists:categories,id'],
            'severity_id' => ['nullable', 'integer', 'exists:severities,id'],
            'assigned_to' => ['nullable', 'integer', 'exists:users,id'],
            'status' => [
                'sometimes', 
                \Illuminate\Validation\Rule::in(['pending', 'validated', 'in_progress', 'resolved', 'rejected', 'closed']),
                new \App\Rules\ValidStatusTransition($incident->status),
            ],
        ];
    }
}
