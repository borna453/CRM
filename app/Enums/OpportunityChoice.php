<?php
namespace App\Enums;

enum OpportunityChoice: string
{
    case Deal = 'Deal';
    case Contract = 'Contract';

    public function label(): string
    {
        return match ($this) {
            self::Deal => __('portal.deals.deal'),
            self::Contract => __('portal.contracts.contract'),
        };
    }

    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn ($case) => [$case->value => $case->label()])
            ->toArray();
    }
}
