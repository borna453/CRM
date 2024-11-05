<?php

namespace App\Utils\DTO;

readonly class TaskDTO
{
    public function __construct(
        public string $title,
        public string $responsible,
        public ?string $completed_by,
        public ?int $user_id
    ) {}

    public static function fromArray(array $task): TaskDTO
    {
        return new self(
            title: $task['title'],
            responsible: $task['responsible'] ?? '',
            completed_by: $task['completed_by'] ?? null,
            user_id: $task['user_id'] ?? null
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
