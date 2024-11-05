<?php

namespace App\Utils\DTO;

use App\Models\User;

readonly class StepDTO
{
    public function __construct(
        public string $title,
        public string $responsible,
        public ?string $completed_by,
        public ?int $user_id
    ) {}

    public static function fromArray(array $task): StepDTO
    {
        return new self(
            title: $task['title'],
            responsible: $task['responsible'] ?? '',
            completed_by: $task['completed_by'] ?? null,
            user_id: $task['user_id'] ?? User::where('name', '=', $task['responsible'])->pluck('id')->first()
        );
    }

    public function toArray(): array
    {
        return [
            'title' => $this->title,
            'responsible' => $this->responsible,
            'completed_by' => $this->completed_by,
            'user_id' => $this->user_id,
        ];
    }
}
