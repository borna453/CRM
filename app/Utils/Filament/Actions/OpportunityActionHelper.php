<?php

namespace App\Utils\Filament\Actions;

use App\Models\Note;
use App\Models\Opportunity;
use App\Models\Task;
use Filament\Tables\Actions\Action;

class OpportunityActionHelper
{
    public static function closeOpportunity(): Action
    {
        return Action::make('close')
            ->visible(fn($record) => !$record->closed_at && auth()->user()->can('update', Opportunity::class))
            ->action(function ($record, $livewire) {
                $record->update([
                    'closed_at' => now(),
                ]);
                $livewire->dispatch('confetti');
                $livewire->dispatch('refreshOpportunities');
            })
            ->icon('heroicon-o-check')
            ->iconButton()
            ->extraAttributes([
                'class' => 'complete-button',
            ]);
    }

    public static function openOpportunity(): Action
    {
        return Action::make('open')
            ->visible(fn($record) => $record->closed_at && auth()->user()->can('update', Opportunity::class))
            ->action(function ($record, $livewire) {
                $record->update([
                    'closed_at' => null,
                ]);
                $livewire->dispatch('refreshOpportunities');
            })
            ->icon('heroicon-o-x-mark')
            ->iconButton()
            ->extraAttributes([
                'class' => 'uncomplete-button',
            ]);
    }

    public static function create($data, $ownerRecordId = null)
    {
        $notesData = $data['notes'];
        $taskData = $data['task'];

        unset($data['task'], $data['notes'], $taskData['is_private']);

        $opportunity = Opportunity::create([
            'title' => $data['title'],
            'text' => $data['text'],
            'label_id' => $data['label_id'],
            'expected_revenue' => $data['expected_revenue'],
            'cost_estimate' => $data['cost_estimate'],
            'company_id' => $data['company_id'] ?? $ownerRecordId ?? null,
        ]);
        if(!empty($notesData['note'])){
            $note = Note::create([
                'note' => $notesData['note'],
                'model_id' => $opportunity->id,
                'model_type' => $opportunity->getMorphClass(),
            ]);
            $opportunity->notes()->save($note);
        }

        if(!empty(array_filter($taskData, function ($a) { return $a !== null;}))){
            $task = Task::create([
                'title' => $taskData['title'] ?? null,
                'information' => $taskData['information'] ?? null,
                'dt_complete_by' => $taskData['dt_complete_by'] ?? null,
                'user_id' => $taskData['user_id'] ?? null,
                'model_id' => $opportunity->id,
                'model_type' => $opportunity->getMorphClass(),
            ]);
            $opportunity->tasks()->save($task);
        }
    }
}
