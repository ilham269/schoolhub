<?php

namespace Tests\Feature;

use App\Models\Guru;
use App\Models\Kelas;
use App\Models\Murid;
use App\Models\Pengumpulantugas;
use App\Models\Subjek;
use App\Models\Tugas;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class MuridTugasNilaiTest extends TestCase
{
    use RefreshDatabase;

    private function tugas(Kelas $kelas, Guru $guru, Subjek $mapel, array $overrides = []): Tugas
    {
        return Tugas::create(array_merge([
            'kelas_id' => $kelas->id,
            'guru_id' => $guru->id,
            'mapel_id' => $mapel->id,
            'judul' => 'Tugas pengujian',
            'tanggal_dibuat' => now(),
            'deadline' => now()->addWeek(),
            'nilai_maksimal' => 100,
            'is_active' => true,
        ], $overrides));
    }

    public function test_murid_hanya_melihat_tugas_aktif_di_kelasnya(): void
    {
        $kelasMurid = Kelas::factory()->create();
        $kelasLain = Kelas::factory()->create();
        $murid = Murid::factory()->create(['kelas_id' => $kelasMurid->id]);
        $guru = Guru::factory()->create();
        $mapel = Subjek::factory()->create();

        $visible = $this->tugas($kelasMurid, $guru, $mapel, ['judul' => 'Tugas kelas sendiri']);
        $this->tugas($kelasMurid, $guru, $mapel, ['judul' => 'Tugas nonaktif', 'is_active' => false]);
        $this->tugas($kelasLain, $guru, $mapel, ['judul' => 'Tugas kelas lain']);

        Sanctum::actingAs($murid->user->fresh());

        $this->getJson('/api/murid/tugas')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $visible->id);
    }

    public function test_nilai_hanya_menghitung_tugas_aktif_murid_di_kelasnya(): void
    {
        $kelasMurid = Kelas::factory()->create();
        $kelasLain = Kelas::factory()->create();
        $murid = Murid::factory()->create(['kelas_id' => $kelasMurid->id]);
        $guru = Guru::factory()->create();
        $mapel = Subjek::factory()->create();
        $tugasMurid = $this->tugas($kelasMurid, $guru, $mapel);
        $tugasLain = $this->tugas($kelasLain, $guru, $mapel);
        $tugasNonaktif = $this->tugas($kelasMurid, $guru, $mapel, ['is_active' => false]);

        Pengumpulantugas::create([
            'tugas_id' => $tugasMurid->id,
            'murid_id' => $murid->id,
            'tanggal_pengumpulan' => now(),
            'nilai' => 88,
            'status' => 'Sudah Dinilai',
        ]);
        Pengumpulantugas::create([
            'tugas_id' => $tugasLain->id,
            'murid_id' => $murid->id,
            'tanggal_pengumpulan' => now(),
            'nilai' => 10,
            'status' => 'Sudah Dinilai',
        ]);
        Pengumpulantugas::create([
            'tugas_id' => $tugasNonaktif->id,
            'murid_id' => $murid->id,
            'tanggal_pengumpulan' => now(),
            'nilai' => 10,
            'status' => 'Sudah Dinilai',
        ]);

        Sanctum::actingAs($murid->user->fresh());

        $this->getJson('/api/murid/nilai')
            ->assertOk()
            ->assertJsonPath('data.summary.tugas_total', 1)
            ->assertJsonPath('data.summary.rata_rata', 88);
    }

    public function test_guru_hanya_melihat_tugas_miliknya_dan_store_mengisi_default(): void
    {
        $kelas = Kelas::factory()->create();
        $guru = Guru::factory()->create();
        $guruLain = Guru::factory()->create();
        $mapel = Subjek::factory()->create();
        $milikGuru = $this->tugas($kelas, $guru, $mapel);
        $this->tugas($kelas, $guruLain, $mapel);

        Sanctum::actingAs($guru->user->fresh());

        $this->getJson('/api/guru/tugas')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $milikGuru->id);

        $this->postJson('/api/guru/tugas', [
            'kelas_id' => $kelas->id,
            'mapel_id' => $mapel->id,
            'judul' => 'Tugas dengan default',
            'deadline' => now()->addDays(3)->toIso8601String(),
        ])->assertCreated()
            ->assertJsonPath('data.is_active', true)
            ->assertJsonPath('data.nilai_maksimal', 100);

        $this->assertDatabaseHas('tugas', [
            'guru_id' => $guru->id,
            'judul' => 'Tugas dengan default',
            'is_active' => true,
            'nilai_maksimal' => 100,
        ]);
        $this->assertNotNull(Tugas::where('judul', 'Tugas dengan default')->value('tanggal_dibuat'));
    }
}
