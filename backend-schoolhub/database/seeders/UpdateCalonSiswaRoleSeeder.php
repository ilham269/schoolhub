<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\CalonSiswa;

class UpdateCalonSiswaRoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * 
     * Seeder ini untuk update role user yang sudah ada dari Murid menjadi Calon_Siswa
     * jika user tersebut memiliki relasi ke tabel calon_siswas
     */
    public function run(): void
    {
        $this->command->info('🔄 Updating existing users with CalonSiswa relation...');
        
        // Get all users yang punya relasi calon_siswa
        $users = User::whereHas('calonSiswa')->where('role', '!=', 'Calon_Siswa')->get();
        
        if ($users->isEmpty()) {
            $this->command->info('✅ No users need to be updated.');
            return;
        }
        
        $count = 0;
        foreach ($users as $user) {
            $oldRole = $user->role;
            $user->role = 'Calon_Siswa';
            $user->save();
            
            $this->command->info("   Updated: {$user->name} ({$user->email}) - {$oldRole} → Calon_Siswa");
            $count++;
        }
        
        $this->command->info('');
        $this->command->info("✅ Successfully updated {$count} user(s) to Calon_Siswa role!");
    }
}
