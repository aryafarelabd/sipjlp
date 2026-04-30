<?php

namespace Database\Seeders;

use App\Models\Pjlp;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class PjlpUserAccountSeeder extends Seeder
{
    public function run(): void
    {
        $pjlps = Pjlp::whereNull('user_id')->get();

        $created = 0;
        $skipped = 0;

        foreach ($pjlps as $pjlp) {
            // Generate username dari nama: "Mutia Rahayu" → "mutia.rahayu"
            $username = $this->generateUsername($pjlp->nama);

            // Pastikan username unik
            $baseUsername = $username;
            $counter = 1;
            while (User::where('username', $username)->exists()) {
                $username = $baseUsername . $counter;
                $counter++;
            }

            // Buat akun user
            $user = User::create([
                'name'      => $pjlp->nama,
                'username'  => $username,
                'email'     => $username . '@sipjlp.local',
                'password'  => Hash::make($username),
                'is_active' => true,
            ]);

            $user->assignRole('pjlp');

            // Link ke PJLP
            $pjlp->update(['user_id' => $user->id]);

            $this->command->line("  ✓ {$pjlp->nama} → username: {$username}");
            $created++;
        }

        $this->command->info("Selesai. Akun dibuat: {$created}, dilewati: {$skipped}.");
        $this->command->warn('Password default = username masing-masing.');
    }

    private function generateUsername(string $nama): string
    {
        // Hapus karakter khusus, lowercase, ganti spasi dengan titik
        $clean = Str::ascii($nama);
        $clean = strtolower(preg_replace('/[^a-zA-Z\s]/', '', $clean));
        $parts  = array_filter(explode(' ', trim($clean)));

        return implode('.', $parts);
    }
}
