<?php

namespace App\Http\Requests;

class StoreMapelRequest extends AcademicFormRequest
{

    public function rules(): array
    {
        return [
            'kode_mapel' => ['required', 'string', 'max:50', 'unique:mapels,kode_mapel'],
            'nama_mapel' => ['required', 'string', 'max:255'],
            'deskripsi' => ['nullable', 'string'],
            'jumlah_jam' => ['nullable', 'integer', 'min:1', 'max:10'],
            'kkm' => ['nullable', 'integer', 'min:0', 'max:100'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }
}
