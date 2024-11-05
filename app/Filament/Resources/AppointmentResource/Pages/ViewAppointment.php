<?php

namespace App\Filament\Resources\AppointmentResource\Pages;

use App\Filament\CustomActions\AppointmentCommentAction;
use App\Filament\Resources\AppointmentResource;
use App\Jobs\SuggestReportTasksJob;
use App\Livewire\OpenPinboardItemsTable;
use App\Models\Appointment;
use App\Models\Report;
use App\Utils\AppointmentDateHelper;
use App\Utils\RichEditorButtons;
use Filament\Actions\Action;
use Filament\Forms\Components\Actions;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Contracts\View\View;
use Illuminate\Support\HtmlString;
use Livewire\Livewire;

/**
 * @property Appointment $record
 */
class ViewAppointment extends EditRecord
{
    protected static string $resource = AppointmentResource::class;

    public bool $openHistory = false;

    public array $tasks = [];

    public ?Report $report = null;

    public function mount(int|string $record): void
    {
        $this->report = Appointment::find($this->record)?->report;

        parent::mount($record);
    }

    public function openHistory(): void
    {
        $this->openHistory = !$this->openHistory;
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('add_tasks')
                ->action(function ($record, $livewire) {
                    $this->tasks =dispatch_sync(new SuggestReportTasksJob($record->report->description, $record, auth()->id()));

                    $livewire->dispatch('open-modal', id: 'suggest-tasks-modal');
                    $livewire->dispatch('refreshTaskTable');
                })
                ->label(__('portal.add_tasks'))
                ->visible(fn($record) => $record->user->login_allowed)
                ->icon('heroicon-o-wrench'),
            Action::make('publish_report')
                ->action(fn() => $this->publishReport())
                ->label(__('portal.appointments.report_publish'))
                ->icon('heroicon-o-check-circle')
                ->requiresConfirmation()
                ->visible(function () {
                    if($this->report){
                        return !$this->report->published_at;
                    }
                }),
            Action::make('appointment_history')
                ->label(__('portal.appointments.history'))
                ->icon('heroicon-o-clock')
                ->visible(fn($record) => $record->user->appointments?->count() > 1)
                ->action(fn() => $this->openHistory()),
            AppointmentCommentAction::make()->visible(fn($record) => $record->report)
        ];
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make([
                    Placeholder::make('user_id')
                        ->label(__('portal.appointments.title'))
                        ->content($this->record->user->name),
                    Actions::make([
                        \Filament\Forms\Components\Actions\Action::make('change')
                            ->label('')
                            ->icon('heroicon-o-pencil')
                            ->outlined()
                            ->color('secondary')
                            ->url(function ($record){
                                return AppointmentResource::getUrl('edit', ['record' => $record]);
                            })
                            ->openUrlInNewTab(),
                    ])->columnStart(2),
                    Placeholder::make('description')
                        ->content(fn () => new HtmlString($this->record->description))
                        ->visible(fn($record) => $record->description)
                        ->label(__('portal.appointments.description'))
                        ->columnStart(1),
                    Placeholder::make('dt_start')
                        ->label(__('portal.appointments.start'))
                        ->content(fn($record) => AppointmentDateHelper::formatDateRange($record))
                        ->columnStart(1),
                ])->columnSpan(1 / 3)->columnStart(1)->disabled(),
                Section::make([
                    RichEditor::make('description')
                        ->label(__('portal.description'))
                        ->reactive()
                        ->live(debounce: 500)
                        ->formatStateUsing(function () {
                            if($this->record->report){
                                return $this->record->report->description;
                            }
                        }),
                    SpatieMediaLibraryFileUpload::make('image-attachments')
                        ->label(__('portal.images'))
                        ->multiple()
                        ->image()
                        ->reorderable()
                        ->collection('image-attachments')
                        ->live(debounce: 10000),
                    SpatieMediaLibraryFileUpload::make('document-attachments')
                        ->label(__('portal.documents'))
                        ->multiple()
                        ->reorderable()
                        ->collection('document-attachments')
                        ->live(debounce: 10000)
                        ->openable(),
                ])->relationship('report')->columnSpan(2),
                Section::make(__('portal.pinboard_items.pinboard'))
                    ->schema([
                        \Filament\Forms\Components\Livewire::make(OpenPinboardItemsTable::class, ['appointment' => $this->record])->columnSpanFull(),
                    ])->collapsible()->collapsed(fn($record) => (!$record->user->pinboardItems->count()) > 0),
                Section::make(__('portal.appointments.internal_notes'))
                    ->schema([
                        RichEditor::make('internal_notes')
                            ->label('')
                            ->default(fn($livewire): string => $livewire->appointment->internal_notes ?? '')
                            ->toolbarButtons(RichEditorButtons::$toolbarButtons),
                    ])->live()->collapsible()->collapsed()
                    ->icon(function () {
                        return $this->record->internal_notes ? 'heroicon-o-information-circle' : null;
                    }),
            ])
            ->columns(3)
            ->statePath('data');
    }

    protected function beforeSave(): void
    {
        if (! empty($this->data['report'])) {
            $this->initializeOrUpdateReport();
        }
    }

    protected function initializeOrUpdateReport(): void
    {
        $reportFields = [
            'description' => $this->data['report']['description'] ?? '',
            'user_id' => $this->record->user_id,
            'title' => $this->record->dt_start->format('d-m-Y') . ' ' . __('portal.appointments.appointment'),
        ];

        if ($this->report) {
            $this->report->update($reportFields);
        } else {
            $this->report = Report::create($reportFields);

            $this->record->update([
                'report_id' => $this->report->id
            ]);
        }

        $this->record->refresh();
        $this->report->refresh();
    }

    public function publishReport(): void
    {
        if (!$this->record->report->published_at) {
            $this->report->publish();
            Notification::make()
                ->title(__('portal.reports.published'))
                ->success()
                ->icon('heroicon-o-check-circle')
                ->body(__('portal.reports.published_message'))
                ->send();
        } else {
            Notification::make()
                ->title(__('portal.reports.already_published'))
                ->icon('heroicon-o-information-circle')
                ->warning()
                ->send();
        }
    }

    public function getTitle(): string|Htmlable
    {
        return __('portal.appointments.view_appointment');
    }

    public function getFooter(): ?View
    {
        return view('filament.resources.appointment-resource.pages.view-appointment', [
            'record' => $this->record,
            'currentReportId' => $this->record->report?->id ?? null,
            'tasks' => $this->tasks,
        ]);
    }
}
