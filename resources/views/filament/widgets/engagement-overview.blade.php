<x-filament::widget>
    <x-filament::card>
        <div class="space-y-6">
            <h3 class="text-xl font-bold">{{ __('portal.messages.engagement_overview') }}</h3>
            <div class="space-y-2">
                <p class="text-sm">{{ __('portal.messages.total_messages') }}: <span class="font-medium">{{ $totalMessages }}</span></p>
                <p class="text-sm">{{ __('portal.messages.total_views') }}: <span class="font-medium">{{ $totalViews }}</span></p>
                <p class="text-sm">{{ __('portal.messages.average_views_per_message') }}: <span class="font-medium">{{ $averageViews }}</span></p>
            </div>

            <div>
                <h4 class="text-lg font-semibold">{{ __('portal.messages.company_engagement') }}</h4>
                <ul class="list-disc list-inside space-y-1 text-sm">
                    @foreach ($companyEngagement as $company)
                        @if($company->views_count > 0)
                            <li>
                                <a href="{{ \App\Filament\Resources\CompanyResource::getUrl('edit', ['record' => $company->id]) }}" class="text-primary-600 hover:underline">
                                    {{ $company->name }}
                                </a>
                                - {{ __('portal.messages.views') }}: <span class="font-medium">{{ $company->views_count }}</span>
                            </li>
                        @endif
                    @endforeach
                </ul>
            </div>

            @if ($topUsers->where('views_count', '>', 0)->isNotEmpty())
                <div>
                    <h4 class="text-lg font-semibold">{{ __('portal.messages.top_engaged_users') }}</h4>
                    <ul class="list-disc list-inside space-y-1 text-sm">
                        @foreach ($topUsers as $user)
                            @if ($user->views_count > 0)
                                <li>
                                    <a href="{{ \App\Filament\Resources\UserResource::getUrl('edit', ['record' => $user->id]) }}" class="text-primary-600 hover:underline">
                                        {{ $user->name }}
                                    </a>
                                    - {{ __('portal.messages.views') }}: <span class="font-medium">{{ $user->views_count }}</span>
                                </li>
                            @endif
                        @endforeach
                    </ul>
                </div>
            @endif

            <div>
                <h4 class="text-lg font-semibold">{{ __('portal.messages.recent_messages') }}</h4>
                <ul class="list-disc list-inside space-y-1 text-sm">
                    @foreach ($recentMessages as $message)
                        <li>
                            <a href="{{ route('filament.admin.resources.messages.index', ['model_id' => $message->id]) }}" class="text-primary-600 hover:underline">
                                {{ $message->title }}
                            </a>
                            - {{ __('portal.messages.views') }}: <span class="font-medium">{{ $message->views_count }}</span>
                            - {{ __('portal.messages.replies') }}: <span class="font-medium">{{ $message->replies_count }}</span>
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>
    </x-filament::card>
</x-filament::widget>
