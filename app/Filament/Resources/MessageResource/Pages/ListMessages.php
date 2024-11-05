<?php

namespace App\Filament\Resources\MessageResource\Pages;

use App\Filament\Resources\MessageResource;
use App\Models\Company;
use App\Models\Message;
use App\Models\User;
use App\Traits\OpensModalOnRedirect;
use App\Utils\Filament\FormFields\MessageHelper;
use Filament\Actions;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Pages\ListRecords;
use Livewire\Attributes\On;
use Livewire\Attributes\Url;

class ListMessages extends ListRecords
{
    use OpensModalOnRedirect;

    #[Url]
    public $model_id;

    protected static string $resource = MessageResource::class;

    protected $listeners = ['refreshListMessages' => '$refresh'];

    public function mountHandlesModalOpening(): void
    {
        $this->model_id = request()?->get('model_id');

        if ($this->model_id) {
            $this->dispatch('openModal');
        }
    }

    #[On('openModal')]
    public function openModal()
    {
        $this->mountTableAction('view', $this->model_id);

        $this->model_id = null;
    }

    public function mount(): void
    {
        parent::mount();

        $this->mountHandlesModalOpening();
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->form(MessageHelper::formFields())->action(function ($data){
                MessageHelper::create($data);

                $this->dispatch('refreshEngagementWidget');
                $this->dispatch('refreshListMessages');
            }),
        ];
    }
}
