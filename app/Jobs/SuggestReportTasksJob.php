<?php

namespace App\Jobs;

use App\Models\User;
use App\Utils\DTO\ContentDTO;
use App\Utils\DTO\TaskDTO;
use Carbon\Carbon;
use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use OpenAI\Laravel\Facades\OpenAI;

class SuggestReportTasksJob
{
    use Dispatchable;
    use InteractsWithQueue;
    use SerializesModels;

    public function __construct(private readonly string $content, private readonly Model $model, private $createdBy)
    {}

    public function handle(): array
    {
        $today = Carbon::today()->toDateString();

        $result = OpenAI::chat()->create([
            'model' => 'gpt-4o-2024-08-06',
            'messages' => [
                [
                    'role' => 'system',
                    'content' => "Vandaag is het ($today). Je krijgt zo de inhoud van een gespreksverslag. Lees deze goed en maak hiervoor taken aan die smart zijn. Je uitvoer is altijd een JSON met een array van taken."
                ],
                ['role' => 'user', 'content' => $this->content],
            ],
            'response_format' => [
                'type' => 'json_schema',
                'json_schema' => [
                    'name' => 'suggest_report_tasks',
                    'schema' => [
                        'type' => 'object',
                        'properties' => [
                            'steps' => [
                                'type' => 'array',
                                'items' => [
                                    'type' => 'object',
                                    'properties' => [
                                        'title' => ['type' => 'string'],
                                        'responsible' => ['type' => 'string'],
                                        'completed_by' => ['type' => 'string'],
                                    ],
                                    'required' => ['title', 'responsible', 'completed_by'],
                                    'additionalProperties' => false,
                                ]
                            ],
                        ],
                        'required' => ['steps'],
                        'additionalProperties' => false,
                    ],
                    'strict' => true,
                ]
            ],
        ]);

        return  ContentDTO::fromArray(json_decode($result->choices[0]->message->content, true))?->toArray();
    }

    public function getContent(): string
    {
        return $this->content;
    }

    public function getModel(): Model
    {
        return $this->model;
    }
}
