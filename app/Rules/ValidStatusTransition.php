<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class ValidStatusTransition implements ValidationRule
{
    /**
     * Create a new rule instance holding the incident's pre-update status natively.
     */
    public function __construct(
        private string $currentStatus
    ) {}

    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        // Matrix strictly controlling logic execution: Map of Current -> Allowed Destination States
        $allowedTransitions = [
            'pending'     => ['validated', 'rejected'],
            'validated'   => ['in_progress', 'rejected'],
            'in_progress' => ['resolved'],
            'resolved'    => ['closed'],
            'closed'      => [],
            'rejected'    => ['pending'], // Optionally recover a historically rejected incident
        ];

        // Ensure purely updating other fields (subject, assignee) does not trigger transition errors.
        if ($this->currentStatus === $value) {
            return;
        }

        // Look up valid transitions checking against strict schema
        $validNextStates = $allowedTransitions[$this->currentStatus] ?? [];

        if (!in_array($value, $validNextStates)) {
            $fail("You cannot transition an incident directly from '{$this->currentStatus}' to '{$value}'. Check the valid workflow sequence.");
        }
    }
}
