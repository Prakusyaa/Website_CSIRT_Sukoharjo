<?php

namespace App\Http\Requests\Incident;

use App\Models\Report;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreIncidentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('create', Report::class);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'subject'        => ['required', 'string', 'max:255'],
            'description'    => ['required', 'string'],
            'category_id'    => ['nullable', 'integer', 'exists:categories,id'],
            'severity_id'    => ['nullable', 'integer', 'exists:severities,id'],
            'assigned_to'    => ['nullable', 'integer', 'exists:users,id'],

            // Reporter type must always be explicit
            'reporter_type'  => ['required', 'in:internal,external'],

            // reporter_id required when internal, null when external
            'reporter_id'    => [
                'required_if:reporter_type,internal',
                'nullable',
                'integer',
                'exists:users,id',
            ],

            // reporter_email required when external, null when internal
            'reporter_email' => [
                'required_if:reporter_type,external',
                'nullable',
                'email',
                'max:255',
            ],

            'attachments'    => ['nullable', 'array', 'max:5'],
            'attachments.*'  => [
                'file',
                'mimetypes:image/jpeg,image/png,application/pdf,text/csv,text/plain,application/zip',
                'extensions:jpg,jpeg,png,pdf,csv,txt,zip',
                'max:10240',
            ],
        ];
    }
}
