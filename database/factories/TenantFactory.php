<?php

namespace Database\Factories;

use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class TenantFactory extends Factory
{
    protected $model = Tenant::class;

    public function definition(): array
    {
        return [
            'id' => $this->faker->company,
            'encryption_key' => $this->faker->word(),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
            'data' => [
                'email' => [
                    'from_name' => $this->faker->word(),
                ],
            ],
        ];
    }

    public static function preview(array $data = []): Tenant
    {
        return Tenant::factory()->make($data);
    }
}
