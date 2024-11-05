<?php

namespace Database\Factories;

use App\Models\Company;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class CompanyFactory extends Factory
{
    protected $model = Company::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->name(),
            'address' => $this->faker->address(),
            'zip_code' => $this->faker->postcode(),
            'city' => $this->faker->city(),
            'email' => $this->faker->unique()->safeEmail(),
            'coc_number' => $this->faker->word(),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
            'tenant_id' => 'cloudmazing',
            'phone_number' => $this->faker->phoneNumber(),
        ];
    }

    public static function preview(array $data = [], ?Company $company = null): Company
    {
        return Company::factory()->make($data);
    }
}
