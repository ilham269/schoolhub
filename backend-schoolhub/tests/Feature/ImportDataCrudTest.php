<?php

namespace Tests\Feature;

use Illuminate\Http\Testing\File;
use Tests\TestCase;

class ImportDataCrudTest extends TestCase
{
    public function test_murid_import_endpoint_accepts_csv_file(): void
    {
        $file = File::create('murid.csv', 200, 'text/csv');
        $file->getContent = fn () => "email,password,nis,nama_lengkap_murid,gender,kelas_id,tanggal_lahir,tempat_lahir,alamat,nomor_telepon\nstudent1@example.com,Secret123,2001,Student Satu,L,1,2009-01-15,Jakarta,Jl. Mawar 1,0811111111\n";

        $response = $this->postJson('/api/murid/import', [
            'file' => $file,
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'imported',
                'failed',
                'errors',
            ]);
    }

    public function test_guru_import_endpoint_accepts_csv_file(): void
    {
        $file = File::create('guru.csv', 200, 'text/csv');
        $file->getContent = fn () => "email,password,nip,nama_lengkap_guru,gender,tanggal_lahir,alamat,nomor_telepon\nguru1@example.com,Secret123,9001,Guru Satu,L,1990-05-10,Jl. Kenanga 8,0812222222\n";

        $response = $this->postJson('/api/guru/import', [
            'file' => $file,
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'imported',
                'failed',
                'errors',
            ]);
    }

    public function test_karyawan_import_endpoint_accepts_csv_file(): void
    {
        $file = File::create('karyawan.csv', 200, 'text/csv');
        $file->getContent = fn () => "email,password,nip,nama_lengkap_karyawan,bagian,nomor_telepon,alamat\nstaff1@example.com,Secret123,8001,Staff Satu,Tata Usaha,0813333333,Jl. Melati 9\n";

        $response = $this->postJson('/api/karyawan/import', [
            'file' => $file,
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'imported',
                'failed',
                'errors',
            ]);
    }
}
