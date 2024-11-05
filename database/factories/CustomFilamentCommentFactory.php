<?php

namespace Database\Factories;

use App\Models\CustomFilamentComment;
use App\Models\Report;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class CustomFilamentCommentFactory extends Factory
{
    protected $model = CustomFilamentComment::class;

    public function definition(): array
    {
        return [
            'user_id' => app()->environment('testing') ? User::factory() : null,
            'subject_type' => Report::class,
            'subject_id' => app()->environment('testing') ? Report::factory() : null,
            'comment' => $this->faker->sentence,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ];
    }

    public static function preview(array $data = [], ?Report $report = null): CustomFilamentComment
    {
        return CustomFilamentComment::factory()->make($data)
            ->setRelation('subject', $report ??= Report::factory()::preview())
            ->setRelation('user', User::factory()::preview());
    }
}
