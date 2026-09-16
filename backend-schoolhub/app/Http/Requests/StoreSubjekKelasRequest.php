<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;

class StoreSubjekKelasRequest extends AcademicFormRequest
{

    public function rules(): array
    {
        return [
            'kelas_id' => ['required', 'integer', 'exists:kelas,id'],
            'mapel_id' => [
                'required',
                'integer',
                'exists:mapels,id',
                Rule::unique('class_subjects', 'mapel_id')->where(
                    fn ($query) => $query->where('kelas_id', $this->input('kelas_id'))
                ),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'mapel_id.unique' => 'Mata pelajaran sudah di-mapping ke kelas ini.',
        ];
    }
}
