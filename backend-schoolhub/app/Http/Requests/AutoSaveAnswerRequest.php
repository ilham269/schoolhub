<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AutoSaveAnswerRequest extends FormRequest
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
            'question_id' => 'required|integer|exists:ppdb_questions,id',
            'option_id' => 'nullable|integer|exists:ppdb_options,id',
            'answer_text' => 'nullable|string|max:10000',
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
            'question_id.required' => 'ID soal wajib diisi.',
            'question_id.exists' => 'Soal tidak ditemukan.',
            'option_id.exists' => 'Pilihan jawaban tidak valid.',
            'answer_text.max' => 'Jawaban terlalu panjang (maksimal 10000 karakter).',
        ];
    }
}
