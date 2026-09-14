<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class LogActivityRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'session_id' => 'required|integer|exists:ppdb_exam_sessions,id',
            'event' => [
                'required',
                'string',
                Rule::in([
                    'tab_switch',
                    'fullscreen_exit',
                    'copy_attempt',
                    'paste_attempt',
                    'right_click',
                    'page_hidden',
                    'page_visible',
                    'network_disconnect',
                ]),
            ],
            'metadata' => 'nullable|array',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'session_id.required' => 'ID sesi ujian wajib diisi.',
            'session_id.exists' => 'Sesi ujian tidak valid.',
            'event.required' => 'Jenis aktivitas wajib diisi.',
            'event.in' => 'Jenis aktivitas tidak valid.',
        ];
    }
}
