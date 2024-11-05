<?php

namespace Database\Factories;

use App\Models\Task;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class TaskFactory extends Factory
{
    protected $model = Task::class;

    public function definition(): array
    {
        return [
            'title' => $this->faker->sentence(6, true),
            'dt_complete_by' => Carbon::now()->addHours(random_int(1,72)),
            'created_at' => Carbon::now()->subDays(random_int(1,30)),
            'created_by' => User::factory(),
            'updated_at' => Carbon::now(),
            'information' => $this->faker->paragraph(),

            'tenant_id' => app()->environment('testing') ? Tenant::factory() : null,
            'user_id' => app()->environment('testing') ? User::factory() : null,
        ];
    }

    public static function preview(array $data = [], ?User $user = null): Task
    {
        return Task::factory()->make($data)
            ->setRelation('user', $user ??= User::factory()->make())
            ->setRelation('created_by', $user ??= User::factory()->make());
    }
}
