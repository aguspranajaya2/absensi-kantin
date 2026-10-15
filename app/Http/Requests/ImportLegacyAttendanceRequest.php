<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class ImportLegacyAttendanceRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->is_admin === true;
    }

    protected function prepareForValidation(): void
    {
        if (strlen($this->getContent()) > 10 * 1024 * 1024) {
            abort(413);
        }

        $payload = $this->all();

        foreach (is_array($payload['users'] ?? null) ? $payload['users'] : [] as $index => $user) {
            if (is_array($user) && is_string($user['id'] ?? null)) {
                $payload['users'][$index]['id'] = Str::upper(trim($user['id']));
            }
        }

        foreach (is_array($payload['logs'] ?? null) ? $payload['logs'] : [] as $index => $log) {
            if (is_array($log) && is_string($log['userId'] ?? null)) {
                $payload['logs'][$index]['userId'] = Str::upper(trim($log['userId']));
            }
        }

        $this->replace($payload);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'users' => ['nullable', 'array', 'max:10000'],
            'users.*' => ['array:id,name,department,phone,email,createdAt'],
            'users.*.id' => ['required', 'string', 'max:24', 'regex:/\A[A-Z0-9. $+%\/-]+\z/'],
            'users.*.name' => ['required', 'string', 'max:80'],
            'users.*.department' => ['nullable', 'string', 'max:60'],
            'users.*.phone' => ['nullable', 'string', 'max:30'],
            'users.*.email' => ['nullable', 'email', 'max:100'],
            'users.*.createdAt' => ['nullable', 'date'],
            'logs' => ['nullable', 'array', 'max:50000'],
            'logs.*' => ['array:id,userId,name,type,at,source'],
            'logs.*.id' => ['nullable', 'string', 'max:80'],
            'logs.*.userId' => ['required', 'string', 'max:24', 'regex:/\A[A-Z0-9. $+%\/-]+\z/'],
            'logs.*.name' => ['required', 'string', 'max:80'],
            'logs.*.type' => ['required', Rule::in(['Masuk', 'Pulang'])],
            'logs.*.at' => ['required', 'date'],
            'logs.*.source' => ['nullable', 'string', 'max:30'],
        ];
    }
}
