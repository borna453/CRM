<?php

namespace Database\Factories;

use App\Models\Report;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class ReportFactory extends Factory
{
    protected $model = Report::class;

    public function definition(): array
    {
        return [
            'title' => $this->faker->word(),
            'description' => $this->faker->text(),
            'tenant_id' => 'cloudmazing',
            'published_at' => Carbon::now(),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),

            'user_id' => app()->environment('testing') ? User::factory() : null,
        ];
    }

    public static function preview(array $data = [], ?User $user = null): Report
    {
        return Report::factory()->make($data)
            ->setRelation('user', $user ??= User::factory()->make());
    }
}
