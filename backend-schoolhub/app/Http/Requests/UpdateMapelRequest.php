<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;

class UpdateMapelRequest extends AcademicFormRequest
{

    public function rules(): array
    {
        $id = $this->route('id');

        return [
            'kode_mapel' => ['sometimes', 'required', 'string', 'max:50', Rule::unique('mapels', 'kode_mapel')->ignore($id)],
            'nama_mapel' => ['sometimes', 'required', 'string', 'max:255'],
            'deskripsi' => ['nullable', 'string'],
            'jumlah_jam' => ['nullable', 'integer', 'min:1', 'max:10'],
            'kkm' => ['nullable', 'integer', 'min:0', 'max:100'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }
}
