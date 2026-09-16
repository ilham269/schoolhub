<?php

namespace App\Http\Requests;

use App\Models\Jadwal;
use App\Models\Subjekguru;
use App\Models\Subjekkelas;
use Illuminate\Validation\Validator;

class StoreJadwalRequest extends AcademicFormRequest
{
    protected function prepareForValidation(): void
    {
        if ($this->filled('ruangan') && ! $this->filled('ruang')) {
            $this->merge(['ruang' => $this->input('ruangan')]);
        }

        foreach (['jam_mulai', 'jam_selesai'] as $field) {
            if ($this->filled($field)) {
                $this->merge([$field => substr((string) $this->input($field), 0, 5)]);
            }
        }
    }

    public function rules(): array
    {
        return [
            'kelas_id' => ['required', 'integer', 'exists:kelas,id'],
            'guru_id' => ['required', 'integer', 'exists:gurus,id'],
            'mapel_id' => ['required', 'integer', 'exists:mapels,id'],
            'hari' => ['required', 'in:Senin,Selasa,Rabu,Kamis,Jumat,Sabtu'],
            'jam_mulai' => ['required', 'date_format:H:i'],
            'jam_selesai' => ['required', 'date_format:H:i', 'after:jam_mulai'],
            'ruang' => ['nullable', 'string', 'max:100'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            if ($validator->errors()->isNotEmpty()) {
                return;
            }

            $this->assertAcademicIntegrity($validator);
        });
    }

    protected function assertAcademicIntegrity(Validator $validator): void
    {
        $guruId = (int) $this->input('guru_id');
        $kelasId = (int) $this->input('kelas_id');
        $mapelId = (int) $this->input('mapel_id');
        $hari = (string) $this->input('hari');
        $start = $this->normalizedTime('jam_mulai');
        $end = $this->normalizedTime('jam_selesai');
        $excludeId = $this->route('id') ? (int) $this->route('id') : null;

        if (! Subjekguru::where('guru_id', $guruId)->where('mapel_id', $mapelId)->exists()) {
            $validator->errors()->add('guru_id', 'Guru tidak mengampu mata pelajaran yang dipilih.');
        }

        if (! Subjekkelas::where('kelas_id', $kelasId)->where('mapel_id', $mapelId)->exists()) {
            $validator->errors()->add('mapel_id', 'Mata pelajaran belum di-mapping ke kelas ini.');
        }

        if (Jadwal::teacherHasConflict($guruId, $hari, $start, $end, $excludeId)) {
            $validator->errors()->add('jam_mulai', 'Guru memiliki jadwal yang bentrok pada hari dan jam tersebut.');
        }

        if (Jadwal::classHasConflict($kelasId, $hari, $start, $end, $excludeId)) {
            $validator->errors()->add('kelas_id', 'Kelas memiliki jadwal yang bentrok pada hari dan jam tersebut.');
        }
    }

    public function normalizedTime(string $field): string
    {
        $value = (string) $this->input($field);

        return strlen($value) === 5 ? $value.':00' : $value;
    }
}
