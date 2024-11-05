<?php

namespace Database\Factories;

use App\Models\Appointment;
use App\Models\Report;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class AppointmentFactory extends Factory
{
    protected $model = Appointment::class;

    public function definition(): array
    {
        return [
            'report_id' => app()->environment('testing') ? Report::factory() : null,
            'title' => $this->faker->word(),
            'description' => $this->faker->text(),
            'internal_notes' => $this->faker->word(),
            'dt_start' => Carbon::now(),
            'dt_end' => Carbon::now()->addHour(),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
            'location' => $this->faker->address(),
            'online_url' => $this->faker->url(),
            'other_location' => $this->faker->address(),
            'tenant_id' => 'cloudmazing',
            'created_by' => app()->environment('testing') ? User::factory() : null,
            'user_id' => app()->environment('testing') ? User::factory() : null,
        ];
    }

    public static function preview(array $data = [], ?Report $report = null, ?User $user = null, ?User $createdBy = null): Appointment
    {
        return Appointment::make(array_merge([
            'report_id' => $report->id ?? null,
            'created_by' => $createdBy->id ?? null,
            'user_id' => $user->id ?? null,
        ], $data))
            ->setRelation('report', $report ?? Report::factory()->make())
            ->setRelation('user', $user ?? User::factory()->make())
            ->setRelation('createdBy', $createdBy ?? User::factory()->make());
    }
}
