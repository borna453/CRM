<?php

namespace App\Filament\Pages;

use App\Enums\Permissions;
use App\Enums\ViewOptions;
use App\Filament\Resources\OpportunityResource;
use App\Models\Label;
use App\Models\Note;
use App\Models\Opportunity;
use App\Models\Task;
use App\Traits\ShowCompanySelectFieldTrait;
use App\Utils\Filament\Actions\OpportunityActionHelper;
use Filament\Actions\CreateAction;
use Filament\Forms\Set;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Collection;
use Livewire\Attributes\On;
use Mokhosh\FilamentKanban\Pages\KanbanBoard;

class OpportunitiesKanbanBoard extends KanbanBoard
{
    use ShowCompanySelectFieldTrait;

    protected static string $recordStatusAttribute = 'label_id';

    protected static ?string $navigationIcon = 'heroicon-o-map';

    protected static ?int $navigationSort = 7;

    protected static string $model = Opportunity::class;

    protected ?string $maxContentWidth = 'full';

    protected static string $recordView = 'filament-kanban::kanban-record';

    protected static string $headerView = 'filament-kanban::kanban-header';

    public int $selectedRecordId;

    protected $listeners = ['refreshNotes' => '$refresh'];

    public $record;
    public $recordId;

    public bool $disableEditModal = false;

    public function mount(): void
    {
        $this->record = $this->form?->getRecord();
        $this->form->fill();
        $this->disableEditModal = !auth()->user()->can('editKanban', Opportunity::class);
    }

    protected function statuses(): Collection
    {
        return Label::showOnBoard()->get()->map(function ($label) {
            return [
                'id' => $label->id,
                'title' => $label->name,
            ];
        });
    }

    #[On('close-modal')]
    public function onClose()
    {
        $this->editModalRecordId = null;
    }

    #[On('openKanbanEdit')]
    public function openKanbanEdit($params)
    {
        $recordId = $params['recordId'];
        $data = $params['data'] ?? [];

        $this->editModalRecordId = $recordId;
        $this->recordId = $recordId;

        if($params){
            $recordData = Opportunity::find($recordId)->toArray();
            $this->form->fill([
                'company_id' => $params['companyId'] ?? null,
                'title' => $recordData['title'],
                'text' => $recordData['text'],
                'expected_revenue' => $recordData['expected_revenue'],
                'label_id' => $recordData['label_id'],
                'hideSelectAfterCompanyCreate' => true,
            ]);
        }
        $this->form($this->form);

        $this->dispatch('open-modal', id: 'kanban--edit-record-modal');
    }


    public function onStatusChanged(int $recordId, string $status, array $fromOrderedIds, array $toOrderedIds): void
    {
        Opportunity::find($recordId)->update(['label_id' => $status]);
        Opportunity::setNewOrder($toOrderedIds);

        $label = Label::find($status);
        if ($label->finished_state) {
            $this->selectedRecordId = $recordId;
            $this->dispatch('open-modal', id: 'choice-opportunity');
        }
    }

    public function onSortChanged(int $recordId, string $status, array $orderedIds): void
    {
        Opportunity::setNewOrder($orderedIds);
    }

    protected function getEditModalFormSchema(?int $recordId): array
    {
        if($recordId){
            $opportunity = Opportunity::find($recordId);
            $this->record = $opportunity;
            return OpportunityResource::getFormSchema(recordId: $opportunity?->id);
        }
        if($this->recordId){
            return OpportunityResource::getFormSchema(recordId:  $this->recordId);

        }
        return [];
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->model(Opportunity::class)
                ->label(__('portal.opportunities.create'))
                ->modalHeading(__('portal.opportunities.create'))
                ->visible(auth()->user()->can('createKanban', Opportunity::class))
                ->form(OpportunityResource::getFormSchema())
                ->action(fn($data) => OpportunityActionHelper::create($data)),
        ];
    }

    public function editModalFormSubmitted(): void
    {
        $data = $this->form->getState();

        unset($data['notes'], $data['task']);

        $this->record->update($data);

        $this->dispatch('close-modal', id: 'kanban--edit-record-modal');
    }

    public function getTitle(): string|Htmlable
    {
        return __('portal.opportunities.kanban_board');
    }

    public static function getNavigationLabel(): string
    {
        return __('portal.opportunities.kanban_board');
    }

    public function archiveOpportunity(Opportunity $opportunity)
    {
        $opportunity->update([
            'closed_at' => now(),
            'label_id' => Label::where('should_archive', '=','1')->first()?->id,
            ]);

        $this->dispatch('close-modal', id: 'choice-opportunity');
    }

    public function winOpportunity()
    {
        $this->redirect(route('filament.admin.pages.choice-deal-contract.{recordId?}', ['recordId' => $this->selectedRecordId]));
    }

    protected function getEditModalTitle(): string
    {
        return __('portal.opportunities.edit');
    }

    protected function getEditModalWidth(): string
    {
        return '4xl';
    }

    public function truncateAndRemoveNewLines($text, $limit = 100) {
        $text = preg_replace('/\s+/', ' ', $text);
        $text = str_replace(['<p>', '</p>'], '', $text);
        $text = preg_replace('/\.(?!\s)/', '. ', $text); // Ensure there's a space after periods
        return strlen($text) > $limit ? substr($text, 0, $limit) . '...' : $text;
    }

    public function closeModal()
    {
        $this->dispatch('close-modal', id: 'choice-opportunity');
    }

    public function openModalOnStatusClick($labelId): void
    {
        $this->dispatch('open-modal',$labelId, null , id: 'create-opportunity-modal-kanban');
    }

    protected function getEditModalSaveButtonLabel(): string
    {
        return __('filament-actions::edit.single.modal.actions.save.label');
    }

    public function getEditModalCancelButtonLabel(): string
    {
        return __('portal.cancel');
    }

    public static function canAccess(): bool
    {
        return auth()->user()->can('viewKanban', Opportunity::class);
    }
}
