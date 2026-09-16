<?php

namespace App\Http\Requests;

use App\Models\Jadwal;

class UpdateJadwalRequest extends StoreJadwalRequest
{
    protected function prepareForValidation(): void
    {
        $jadwal = Jadwal::find($this->route('id'));

        if ($jadwal) {
            $this->merge([
                'kelas_id' => $this->input('kelas_id', $jadwal->kelas_id),
                'guru_id' => $this->input('guru_id', $jadwal->guru_id),
                'mapel_id' => $this->input('mapel_id', $jadwal->mapel_id),
                'hari' => $this->input('hari', $jadwal->hari),
                'jam_mulai' => $this->input('jam_mulai', substr((string) $jadwal->jam_mulai, 0, 5)),
                'jam_selesai' => $this->input('jam_selesai', substr((string) $jadwal->jam_selesai, 0, 5)),
                'ruang' => $this->input('ruang', $this->input('ruangan', $jadwal->ruang)),
            ]);
        }

        parent::prepareForValidation();
    }
}
