<?php

it('processes tasks correctly from OpenAI response', function () {
    Mockery::mock('alias:OpenAI\Laravel\Facades\OpenAI')
        ->shouldReceive('chat->create')
        ->andReturn((object)[
            'choices' => [
                (object)[
                    'message' => (object)[
                        'content' => json_encode([
                            'steps' => [
                                (object)[
                                    'title' => 'Sample Task',
                                    'completed_by' => '2024-05-20',
                                    'responsible' => 'Regular User'
                                ]
                            ]
                        ])
                    ]
                ]
            ]
        ]);

    $model = new App\Models\Report(['id' => 1]);

    $job = new \App\Jobs\SuggestReportTasksJob('content', $model, $this->adminUser->id);
    $result = $job->handle();

    $this->assertCount(1, $result);
    $this->assertArrayHasKey('title', $result[0]);
    $this->assertArrayHasKey('completed_by', $result[0]);
    $this->assertArrayHasKey('responsible', $result[0]);
    $this->assertEquals('Sample Task', $result[0]['title']);
    $this->assertEquals('2024-05-20', $result[0]['completed_by']);
    $this->assertEquals('Regular User', $result[0]['responsible']);
});
