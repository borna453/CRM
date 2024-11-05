<?php

namespace App\Utils\DTO;

readonly class ContentDTO
{
    public function __construct(
        public readonly ?array $steps
    ) {}

    public static function fromArray(array $task): ContentDTO
    {
        return new self(
            steps: array_map(function (array $step) {
                return StepDTO::fromArray($step);
            }, $task['steps'] ?? [])
        );
    }

    public function toArray()
    {
        return array_map(function (StepDTO $step) {
            return $step->toArray();
        }, $this->steps ?? []);
    }
}
