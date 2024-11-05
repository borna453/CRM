@php
    /** @var \App\Enums\NotificationTypeEnum $type */
    /** @var array $params */
@endphp

<div>
    <table class="fi-ta-table w-full table-auto divide-y divide-gray-200 text-start text-sm dark:divide-white/5">
        <thead class="divide-y divide-gray-200 dark:divide-white/5">
        <tr class="bg-gray-50 dark:bg-white/5">
            <th class="fi-ta-header-cell px-3 py-3.5 sm:first-of-type:ps-6 sm:last-of-type:pe-6 fi-table-header-cell-type" width="25%">
                    <span class="group flex w-full items-center gap-x-1 whitespace-nowrap justify-start">
                        <span class="fi-ta-header-cell-label text-sm font-semibold text-gray-950 dark:text-white">
                            {{__('portal.notifications.types.parameters')}}
                        </span>
                    </span>
            </th>
            <th class="fi-ta-header-cell px-3 py-3.5 sm:first-of-type:ps-6 sm:last-of-type:pe-6 fi-table-header-cell-type">
                    <span class="group flex w-full items-center gap-x-1 whitespace-nowrap justify-start">
                        <span class="fi-ta-header-cell-label text-sm font-semibold text-gray-950 dark:text-white">
                            {{__('portal.notifications.types.parameter_explanation')}}
                        </span>
                    </span>
            </th>
        </tr>
        </thead>
        <tbody class="divide-y divide-gray-200 whitespace-nowrap dark:divide-white/5">
        @foreach($params as $param => $description)
            <tr class="fi-ta-row [@media(hover:hover)]:transition [@media(hover:hover)]:duration-75 hover:bg-gray-50 dark:hover:bg-white/5">
                <td
                    x-data="{ tooltip: @js(__('portal.notifications.types.copy_to_clipboard')) }"
                    class="fi-ta-cell p-0 first-of-type:ps-1 last-of-type:pe-1 sm:first-of-type:ps-3 sm:last-of-type:pe-3 fi-table-cell-type cursor-pointer"
                    @click="
                        navigator.clipboard.writeText('{'+'{{ $param }}'+'}');
                        $tooltip(@js(__('portal.notifications.types.copied')));
                    ">
                    <div class="fi-ta-col-wrp fi-ta-text grid w-full gap-y-1 px-3 py-4" x-tooltip.interactive="tooltip">
                        <div class="flex items-center gap-3">
                            <button type="button" class="copy-to-clipboard">
                                <x-heroicon-o-clipboard-document-list class="w-5 h-5 text-gray-500 dark:text-white/50" />
                            </button>
                            {{ $param }}
                        </div>
                    </div>
                </td>
                <td class="fi-ta-cell p-0 first-of-type:ps-1 last-of-type:pe-1 sm:first-of-type:ps-3 sm:last-of-type:pe-3 fi-table-cell-type">
                    <div class="fi-ta-col-wrp fi-ta-text grid w-full gap-y-1 px-3 py-4">
                        {{ $description }}
                    </div>
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div>
