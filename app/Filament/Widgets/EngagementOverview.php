<?php

namespace App\Filament\Widgets;

use App\Enums\Permissions;
use App\Models\Company;
use App\Models\Message;
use App\Models\Recipient;
use App\Models\User;
use Filament\Widgets\Widget;

class EngagementOverview extends Widget
{
    protected static string $view = 'filament.widgets.engagement-overview';

    protected static ?int $sort = 2;

    protected $listeners = ['refreshEngagementWidget' => '$refresh'];

    protected function getViewData(): array
    {
        $totalMessages = Message::whereNull('parent_id')->count();

        $totalViews = Recipient::whereHas('message', function ($query) {
            $query->whereNull('parent_id');
        })->whereNotNull('seen_at')->count();

        $averageViews = number_format($totalViews / max($totalMessages, 1), 2);


        $companyEngagement = Company::withMessagesAndViews()->get();


        $topUsers = User::withCount(['recipients as views_count' => function ($query) {
            $query->whereNotNull('seen_at')->whereHas('message', function ($query) {
                $query->whereNull('parent_id');
            });
        }])->orderBy('views_count', 'desc')->limit(10)->get();

        $recentMessages = Message::withCount(['recipients as views_count' => function ($query) {
            $query->whereNotNull('seen_at');
        }, 'replies'])
        ->whereNull('parent_id')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        return [
            'totalMessages' => $totalMessages,
            'totalViews' => $totalViews,
            'averageViews' => $averageViews,
            'companyEngagement' => $companyEngagement,
            'topUsers' => $topUsers,
            'recentMessages' => $recentMessages,
        ];
    }

    public static function canView(): bool
    {
        return auth()->user()->hasTenantPermissionTo(Permissions::VIEW_ENGAGEMENT_OVERVIEW_WIDGET->value);
    }
}
