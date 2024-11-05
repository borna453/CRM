<?php

namespace App\Enums;

enum LabelTypes: string
{
    case Opportunity = 'opportunity';
    case Contract = 'contract';
    case Deals = 'deals';

    public function label(): string
    {
        return match ($this) {
            self::Opportunity => __('portal.opportunities.opportunity'),
            self::Contract => __('portal.contracts.contract'),
            self::Deals => __('portal.deals.deal'),
        };
    }

    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn ($case) => [$case->value => $case->label()])
            ->toArray();
    }
}
