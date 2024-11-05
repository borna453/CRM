<?php

namespace Database\Factories;

use App\Models\PinboardItem;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class PinboardItemFactory extends Factory
{
    protected $model = PinboardItem::class;

    public function definition(): array
    {
        return [
            'description' => $this->faker->text(),
            'dt_is_completed' => null,
            'created_by' => app()->environment('testing') ? User::factory() : null,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),

            'user_id' => app()->environment('testing') ? User::factory() : null,
        ];
    }

    public static function preview(array $data = [], ?User $user = null): PinboardItem
    {
        return PinboardItem::factory()->make($data)
            ->setRelation('user', $user ??= User::factory()->make())
            ->setRelation('createdBy', $user ??= User::factory()->make());
    }
}
