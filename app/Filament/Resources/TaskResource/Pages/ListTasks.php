<?php

namespace App\Filament\Resources\TaskResource\Pages;

use App\Enums\OnboardingTypes;
use App\Filament\Resources\TaskResource;
use App\Models\User;
use App\Traits\OpensModalOnRedirect;
use App\Utils\Filament\Actions\OnboardingActionAttributeHelper;
use App\Utils\PresetViews\TasksPresetViewsHelper;
use Archilex\AdvancedTables\AdvancedTables;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Tables\Table;
use Request;

class ListTasks extends ListRecords
{
    use AdvancedTables;
    use OpensModalOnRedirect;

    protected static string $resource = TaskResource::class;

    public $isOnboarding = false;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->fillForm(fn() => $this->isOnboarding ? ['user_id' => User::role(User::EMPLOYEE)->first()->id] : []),
        ];
    }

    public function getTable(): Table
    {
        return parent::getTable()->paginated(!($this->getActivePresetViewLabel() === __('portal.open')));
    }

    public function getPresetViews(): array
    {
        return TasksPresetViewsHelper::presetViews();
    }

    public function mount(): void
    {
        parent::mount();

        $this->mountHandlesModalOpening();

        $this->isOnboarding = Request::get('onboard_assign_task') === '1';
    }
}
