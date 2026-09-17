<?php

namespace Tests\Feature;

use App\Models\Guru;
use App\Models\Jadwal;
use App\Models\Kelas;
use App\Models\Murid;
use App\Models\Subjek;
use App\Models\Subjekguru;
use App\Models\Subjekkelas;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AcademicCoreTest extends TestCase
{
    use RefreshDatabase;

    private function actingAsAdmin(): User
    {
        $user = User::factory()->create(['role' => 'Admin']);
        Sanctum::actingAs($user);

        return $user;
    }

    private function actingAsGuru(): Guru
    {
        $guru = Guru::factory()->create();
        Sanctum::actingAs($guru->user->fresh());

        return $guru->fresh();
    }

    private function actingAsMurid(?Kelas $kelas = null): Murid
    {
        $murid = Murid::factory()->create([
            'kelas_id' => $kelas?->id ?? Kelas::factory(),
        ]);
        Sanctum::actingAs($murid->user->fresh());

        return $murid->fresh();
    }

    private function payload(array $overrides = []): array
    {
        return array_merge([
            'kode_mapel' => 'PW01',
            'nama_mapel' => 'Pemrograman Web',
            'deskripsi' => 'Dasar web',
            'jumlah_jam' => 4,
            'kkm' => 75,
            'is_active' => true,
        ], $overrides);
    }

    public function test_admin_can_create_mapel(): void
    {
        $this->actingAsAdmin();

        $this->postJson('/api/mapel', $this->payload())
            ->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.kode_mapel', 'PW01');

        $this->assertDatabaseHas('mapels', ['kode_mapel' => 'PW01']);
    }

    public function test_non_admin_cannot_create_mapel(): void
    {
        $this->actingAsGuru();

        $this->postJson('/api/mapel', $this->payload())
            ->assertForbidden()
            ->assertJsonPath('success', false);
    }

    public function test_unauthenticated_cannot_access_mapel(): void
    {
        $this->getJson('/api/mapel')->assertUnauthorized();
    }

    public function test_duplicate_teacher_subject_is_rejected(): void
    {
        $this->actingAsAdmin();
        $guru = Guru::factory()->create();
        $mapel = Subjek::factory()->create();

        Subjekguru::create([
            'guru_id' => $guru->id,
            'mapel_id' => $mapel->id,
        ]);

        $this->postJson('/api/subjek-guru', [
            'guru_id' => $guru->id,
            'mapel_id' => $mapel->id,
        ])->assertStatus(422);
    }

    public function test_duplicate_class_subject_is_rejected(): void
    {
        $this->actingAsAdmin();
        $kelas = Kelas::factory()->create();
        $mapel = Subjek::factory()->create();

        Subjekkelas::create([
            'kelas_id' => $kelas->id,
            'mapel_id' => $mapel->id,
        ]);

        $this->postJson('/api/subjek-kelas', [
            'kelas_id' => $kelas->id,
            'mapel_id' => $mapel->id,
        ])->assertStatus(422);
    }

    public function test_schedule_rejects_teacher_without_subject_mapping(): void
    {
        $this->actingAsAdmin();
        $kelas = Kelas::factory()->create();
        $guru = Guru::factory()->create();
        $mapel = Subjek::factory()->create();

        Subjekkelas::create(['kelas_id' => $kelas->id, 'mapel_id' => $mapel->id]);

        $this->postJson('/api/jadwal', [
            'kelas_id' => $kelas->id,
            'guru_id' => $guru->id,
            'mapel_id' => $mapel->id,
            'hari' => 'Senin',
            'jam_mulai' => '07:00',
            'jam_selesai' => '08:30',
            'ruang' => 'Lab 1',
        ])->assertStatus(422)
            ->assertJsonPath('success', false);
    }

    public function test_schedule_rejects_subject_not_mapped_to_class(): void
    {
        $this->actingAsAdmin();
        $kelas = Kelas::factory()->create();
        $guru = Guru::factory()->create();
        $mapel = Subjek::factory()->create();

        Subjekguru::create(['guru_id' => $guru->id, 'mapel_id' => $mapel->id]);

        $this->postJson('/api/jadwal', [
            'kelas_id' => $kelas->id,
            'guru_id' => $guru->id,
            'mapel_id' => $mapel->id,
            'hari' => 'Senin',
            'jam_mulai' => '07:00',
            'jam_selesai' => '08:30',
        ])->assertStatus(422);
    }

    public function test_schedule_rejects_teacher_time_overlap(): void
    {
        $this->actingAsAdmin();
        $kelasA = Kelas::factory()->create();
        $kelasB = Kelas::factory()->create();
        $guru = Guru::factory()->create();
        $mapelA = Subjek::factory()->create();
        $mapelB = Subjek::factory()->create();

        Subjekguru::create(['guru_id' => $guru->id, 'mapel_id' => $mapelA->id]);
        Subjekguru::create(['guru_id' => $guru->id, 'mapel_id' => $mapelB->id]);
        Subjekkelas::create(['kelas_id' => $kelasA->id, 'mapel_id' => $mapelA->id]);
        Subjekkelas::create(['kelas_id' => $kelasB->id, 'mapel_id' => $mapelB->id]);

        Jadwal::create([
            'kelas_id' => $kelasA->id,
            'guru_id' => $guru->id,
            'mapel_id' => $mapelA->id,
            'hari' => 'Senin',
            'jam_mulai' => '07:00:00',
            'jam_selesai' => '08:30:00',
            'ruang' => 'Lab 1',
            'is_active' => true,
        ]);

        $this->postJson('/api/jadwal', [
            'kelas_id' => $kelasB->id,
            'guru_id' => $guru->id,
            'mapel_id' => $mapelB->id,
            'hari' => 'Senin',
            'jam_mulai' => '07:30',
            'jam_selesai' => '09:00',
        ])->assertStatus(422);
    }

    public function test_schedule_rejects_class_time_overlap(): void
    {
        $this->actingAsAdmin();
        $kelas = Kelas::factory()->create();
        $guruA = Guru::factory()->create();
        $guruB = Guru::factory()->create();
        $mapelA = Subjek::factory()->create();
        $mapelB = Subjek::factory()->create();

        Subjekguru::create(['guru_id' => $guruA->id, 'mapel_id' => $mapelA->id]);
        Subjekguru::create(['guru_id' => $guruB->id, 'mapel_id' => $mapelB->id]);
        Subjekkelas::create(['kelas_id' => $kelas->id, 'mapel_id' => $mapelA->id]);
        Subjekkelas::create(['kelas_id' => $kelas->id, 'mapel_id' => $mapelB->id]);

        Jadwal::create([
            'kelas_id' => $kelas->id,
            'guru_id' => $guruA->id,
            'mapel_id' => $mapelA->id,
            'hari' => 'Senin',
            'jam_mulai' => '07:00:00',
            'jam_selesai' => '08:30:00',
            'is_active' => true,
        ]);

        $this->postJson('/api/jadwal', [
            'kelas_id' => $kelas->id,
            'guru_id' => $guruB->id,
            'mapel_id' => $mapelB->id,
            'hari' => 'Senin',
            'jam_mulai' => '08:00',
            'jam_selesai' => '09:30',
        ])->assertStatus(422);
    }

    public function test_valid_schedule_can_be_created(): void
    {
        $this->actingAsAdmin();
        $kelas = Kelas::factory()->create();
        $guru = Guru::factory()->create();
        $mapel = Subjek::factory()->create();

        Subjekguru::create(['guru_id' => $guru->id, 'mapel_id' => $mapel->id]);
        Subjekkelas::create(['kelas_id' => $kelas->id, 'mapel_id' => $mapel->id]);

        $this->postJson('/api/jadwal', [
            'kelas_id' => $kelas->id,
            'guru_id' => $guru->id,
            'mapel_id' => $mapel->id,
            'hari' => 'Senin',
            'jam_mulai' => '07:00',
            'jam_selesai' => '08:30',
            'ruang' => 'Lab 1',
        ])->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.hari', 'Senin');
    }

    public function test_guru_only_sees_own_schedule(): void
    {
        $guru = $this->actingAsGuru();
        $other = Guru::factory()->create();
        $kelas = Kelas::factory()->create();
        $mapel = Subjek::factory()->create();
        $otherMapel = Subjek::factory()->create();

        Subjekguru::create(['guru_id' => $guru->id, 'mapel_id' => $mapel->id]);
        Subjekguru::create(['guru_id' => $other->id, 'mapel_id' => $otherMapel->id]);
        Subjekkelas::create(['kelas_id' => $kelas->id, 'mapel_id' => $mapel->id]);
        Subjekkelas::create(['kelas_id' => $kelas->id, 'mapel_id' => $otherMapel->id]);

        Jadwal::create([
            'kelas_id' => $kelas->id,
            'guru_id' => $guru->id,
            'mapel_id' => $mapel->id,
            'hari' => 'Senin',
            'jam_mulai' => '07:00:00',
            'jam_selesai' => '08:30:00',
            'is_active' => true,
        ]);
        Jadwal::create([
            'kelas_id' => $kelas->id,
            'guru_id' => $other->id,
            'mapel_id' => $otherMapel->id,
            'hari' => 'Senin',
            'jam_mulai' => '09:00:00',
            'jam_selesai' => '10:30:00',
            'is_active' => true,
        ]);

        $this->getJson('/api/jadwal')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.guru_id', $guru->id);
    }

    public function test_murid_only_sees_class_schedule(): void
    {
        $kelas = Kelas::factory()->create();
        $otherKelas = Kelas::factory()->create();
        $this->actingAsMurid($kelas);

        $guru = Guru::factory()->create();
        $mapel = Subjek::factory()->create();
        $otherMapel = Subjek::factory()->create();

        Subjekguru::create(['guru_id' => $guru->id, 'mapel_id' => $mapel->id]);
        Subjekguru::create(['guru_id' => $guru->id, 'mapel_id' => $otherMapel->id]);
        Subjekkelas::create(['kelas_id' => $kelas->id, 'mapel_id' => $mapel->id]);
        Subjekkelas::create(['kelas_id' => $otherKelas->id, 'mapel_id' => $otherMapel->id]);

        Jadwal::create([
            'kelas_id' => $kelas->id,
            'guru_id' => $guru->id,
            'mapel_id' => $mapel->id,
            'hari' => 'Senin',
            'jam_mulai' => '07:00:00',
            'jam_selesai' => '08:30:00',
            'is_active' => true,
        ]);
        Jadwal::create([
            'kelas_id' => $otherKelas->id,
            'guru_id' => $guru->id,
            'mapel_id' => $otherMapel->id,
            'hari' => 'Senin',
            'jam_mulai' => '09:00:00',
            'jam_selesai' => '10:30:00',
            'is_active' => true,
        ]);

        $this->getJson('/api/jadwal')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.kelas_id', $kelas->id);
    }

    public function test_mapel_update_ignores_own_kode_as_duplicate(): void
    {
        $this->actingAsAdmin();
        $mapel = Subjek::factory()->create(['kode_mapel' => 'BD01']);

        $this->putJson("/api/mapel/{$mapel->id}", [
            'kode_mapel' => 'BD01',
            'nama_mapel' => 'Basis Data Lanjut',
        ])->assertOk()
            ->assertJsonPath('data.nama_mapel', 'Basis Data Lanjut');
    }

    public function test_admin_can_list_update_and_delete_mapel(): void
    {
        $this->actingAsAdmin();
        $mapel = Subjek::factory()->create(['kode_mapel' => 'PWB01']);

        $this->getJson('/api/mapel')->assertOk()->assertJsonFragment(['id' => $mapel->id]);
        $this->putJson("/api/mapel/{$mapel->id}", ['nama_mapel' => 'Pemrograman Web Lanjut'])
            ->assertOk()->assertJsonPath('data.nama_mapel', 'Pemrograman Web Lanjut');
        $this->deleteJson("/api/mapel/{$mapel->id}")->assertOk()->assertJsonPath('success', true);
        $this->assertDatabaseMissing('mapels', ['id' => $mapel->id]);
    }

    public function test_store_mapel_uses_academic_defaults(): void
    {
        $this->actingAsAdmin();

        $this->postJson('/api/mapel', ['kode_mapel' => 'MTK01', 'nama_mapel' => 'Matematika'])
            ->assertCreated()
            ->assertJsonPath('data.jumlah_jam', 2)
            ->assertJsonPath('data.kkm', 75)
            ->assertJsonPath('data.is_active', true);
    }

    public function test_non_admin_cannot_update_or_delete_mapel(): void
    {
        $mapel = Subjek::factory()->create();
        $this->actingAsGuru();

        $this->putJson("/api/mapel/{$mapel->id}", ['nama_mapel' => 'Tidak boleh'])
            ->assertForbidden()->assertJsonPath('success', false);
        $this->deleteJson("/api/mapel/{$mapel->id}")
            ->assertForbidden()->assertJsonPath('success', false);
    }

    public function test_invalid_time_range_is_rejected(): void
    {
        $this->actingAsAdmin();
        $kelas = Kelas::factory()->create();
        $guru = Guru::factory()->create();
        $mapel = Subjek::factory()->create();
        Subjekguru::create(['guru_id' => $guru->id, 'mapel_id' => $mapel->id]);
        Subjekkelas::create(['kelas_id' => $kelas->id, 'mapel_id' => $mapel->id]);

        $this->postJson('/api/jadwal', [
            'kelas_id' => $kelas->id,
            'guru_id' => $guru->id,
            'mapel_id' => $mapel->id,
            'hari' => 'Senin',
            'jam_mulai' => '09:00',
            'jam_selesai' => '08:00',
        ])->assertStatus(422);
    }
}
