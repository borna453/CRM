<?php

namespace App\Traits;

use Livewire\Attributes\On;
use Livewire\Attributes\Url;

trait OpensModalOnRedirect
{
    #[Url]
    public $model_id;

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
        $this->mountTableAction('edit', $this->model_id);

        $this->model_id = null;
    }
}
