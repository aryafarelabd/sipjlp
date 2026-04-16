<?php

namespace Database\Factories;

use App\Enums\StatusPjlp;
use App\Enums\UnitType;
use Illuminate\Database\Eloquent\Factories\Factory;

class PjlpFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id'           => null,
            'nip'               => fake()->unique()->numerify('##############'),
            'badge_number'      => fake()->unique()->bothify('??-###'),
            'nama'              => fake()->name(),
            'unit'              => fake()->randomElement(UnitType::cases()),
            'jabatan'           => fake()->jobTitle(),
            'no_telp'           => fake()->phoneNumber(),
            'alamat'            => fake()->address(),
            'tanggal_bergabung' => fake()->date(),
            'status'            => StatusPjlp::AKTIF,
        ];
    }
}
