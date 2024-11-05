<?php

namespace App\Observers;

use App\Models\Label;

class LabelObserver
{
    public function creating(Label $label): void
    {
        $label->order_column = Label::max('order_column') + 1;
    }
}
