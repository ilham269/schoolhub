<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\{User, CalonSiswa, Guru, PpdbExam, PpdbQuestion, PpdbOption};
use Illuminate\Support\Facades\Hash;

class PpdbTestSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create Admin
        $admin = User::firstOrCreate(
            ['email' => 'admin@test.com'],
            [
                'name' => 'Admin Test',
                'password' => Hash::make('password'),
                'role' => 'Admin',
                'is_active' => true,
            ]
        );

        // Create Guru
        $guruUser = User::firstOrCreate(
            ['email' => 'guru@test.com'],
            [
                'name' => 'Guru Test',
                'password' => Hash::make('password'),
                'role' => 'Guru',
                'is_active' => true,
            ]
        );

        Guru::firstOrCreate(
            ['user_id' => $guruUser->id],
            [
                'nip' => '1234567890',
                'nama_lengkap_guru' => 'Guru Test',
                'gender' => 'L',
                'alamat' => 'Jl. Test No. 123',
                'nomor_telepon' => '081234567890',
            ]
        );

        // Create Student (Calon Siswa) - using new Calon_Siswa role
        $siswaUser = User::firstOrCreate(
            ['email' => 'siswa@test.com'],
            [
                'name' => 'Siswa Test',
                'password' => Hash::make('password'),
                'role' => 'Calon_Siswa',
                'is_active' => true,
            ]
        );

        CalonSiswa::firstOrCreate(
            ['user_id' => $siswaUser->id],
            [
                'nama' => 'Siswa Test',
                'email' => 'siswa@test.com',
                'nisn' => '0123456789',
                'no_hp' => '081234567890',
                'asal_sekolah' => 'SMP Test',
                'jurusan' => 'IPA',
                'status' => 'Terverifikasi',
            ]
        );

        // Create another student
        $siswa2User = User::firstOrCreate(
            ['email' => 'siswa2@test.com'],
            [
                'name' => 'Siswa Test 2',
                'password' => Hash::make('password'),
                'role' => 'Calon_Siswa',
                'is_active' => true,
            ]
        );

        CalonSiswa::firstOrCreate(
            ['user_id' => $siswa2User->id],
            [
                'nama' => 'Siswa Test 2',
                'email' => 'siswa2@test.com',
                'nisn' => '9876543210',
                'no_hp' => '081234567891',
                'asal_sekolah' => 'SMP Test 2',
                'jurusan' => 'IPS',
                'status' => 'Terverifikasi',
            ]
        );

        // Create Sample Exam
        $exam = PpdbExam::firstOrCreate(
            ['title' => 'Ujian PPDB 2026'],
            [
                'created_by' => $admin->id,
                'description' => 'Ujian Penerimaan Peserta Didik Baru tahun 2026',
                'start_at' => now()->subDay(), // Start from yesterday
                'end_at' => now()->addDays(30), // End in 30 days
                'duration_minutes' => 60,
                'passing_score' => 70,
                'is_published' => true,
                'show_result' => true,
            ]
        );

        // Create Questions
        $questions = [
            [
                'question' => 'Apa ibu kota Indonesia?',
                'options' => [
                    ['text' => 'Jakarta', 'correct' => true],
                    ['text' => 'Bandung', 'correct' => false],
                    ['text' => 'Surabaya', 'correct' => false],
                    ['text' => 'Medan', 'correct' => false],
                ],
            ],
            [
                'question' => 'Siapa presiden pertama Indonesia?',
                'options' => [
                    ['text' => 'Soekarno', 'correct' => true],
                    ['text' => 'Soeharto', 'correct' => false],
                    ['text' => 'Habibie', 'correct' => false],
                    ['text' => 'Megawati', 'correct' => false],
                ],
            ],
            [
                'question' => 'Berapa hasil dari 15 + 25?',
                'options' => [
                    ['text' => '30', 'correct' => false],
                    ['text' => '35', 'correct' => false],
                    ['text' => '40', 'correct' => true],
                    ['text' => '45', 'correct' => false],
                ],
            ],
            [
                'question' => 'Apa bahasa pemrograman yang digunakan untuk web development?',
                'options' => [
                    ['text' => 'Python', 'correct' => false],
                    ['text' => 'JavaScript', 'correct' => true],
                    ['text' => 'C++', 'correct' => false],
                    ['text' => 'Java', 'correct' => false],
                ],
            ],
            [
                'question' => 'Planet terbesar di tata surya adalah?',
                'options' => [
                    ['text' => 'Mars', 'correct' => false],
                    ['text' => 'Bumi', 'correct' => false],
                    ['text' => 'Jupiter', 'correct' => true],
                    ['text' => 'Saturnus', 'correct' => false],
                ],
            ],
        ];

        foreach ($questions as $index => $qData) {
            $question = PpdbQuestion::firstOrCreate(
                [
                    'exam_id' => $exam->id,
                    'question' => $qData['question'],
                ],
                [
                    'type' => 'multiple_choice',
                    'score' => 20,
                    'order' => $index + 1,
                ]
            );

            foreach ($qData['options'] as $optIndex => $optData) {
                PpdbOption::firstOrCreate(
                    [
                        'question_id' => $question->id,
                        'option_text' => $optData['text'],
                    ],
                    [
                        'is_correct' => $optData['correct'],
                        'order' => $optIndex + 1,
                    ]
                );
            }
        }

        $this->command->info('✅ Test data created successfully!');
        $this->command->info('');
        $this->command->info('Login credentials:');
        $this->command->info('Admin: admin@test.com / password');
        $this->command->info('Guru: guru@test.com / password');
        $this->command->info('Siswa 1: siswa@test.com / password');
        $this->command->info('Siswa 2: siswa2@test.com / password');
    }
}
