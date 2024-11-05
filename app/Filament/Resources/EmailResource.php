<?php

namespace App\Filament\Resources;

use App\Models\Email;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class EmailResource extends \Cloudmazing\FilamentEmailLog\Filament\Resources\EmailResource
{
    protected static ?string $model = \Cloudmazing\FilamentEmailLog\Models\Email::class;

    protected static ?int $navigationSort = 4;

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('tenant_id', auth()->user()->tenant_id);
    }

    public static function canViewAny(): bool
    {
        return auth()->user()->can('viewAny', Email::class);
    }
}
