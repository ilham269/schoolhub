<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;

class StoreSubjekGuruRequest extends AcademicFormRequest
{

    public function rules(): array
    {
        return [
            'guru_id' => ['required', 'integer', 'exists:gurus,id'],
            'mapel_id' => [
                'required',
                'integer',
                'exists:mapels,id',
                Rule::unique('teacher_subjects', 'mapel_id')->where(
                    fn ($query) => $query->where('guru_id', $this->input('guru_id'))
                ),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'mapel_id.unique' => 'Guru sudah di-mapping ke mata pelajaran ini.',
        ];
    }
}
