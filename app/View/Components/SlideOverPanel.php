<?php

namespace App\View\Components;

use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class SlideOverPanel extends Component
{
    public function __construct(
        public string $title,
        public string $openVariable,
        public string $widthClass = 'max-w-xl',
        public ?string $content = null
    ) {

    }
    public function render(): View
    {
        return view('components.slide-over-panel');
    }
}
