<div class="p-1.5 space-y-3">
    <h2 class="text-xl font-semibold text-gray-900 dark:text-gray-100">{{ $message['title'] }}</h2>
    <div class="prose max-w-full text-gray-700 dark:text-gray-300">
        {!! $message['content'] !!}
    </div>
    <p class="text-sm text-gray-500 dark:text-gray-400">
        {{ __('portal.sent_by') }}: <span class="font-medium text-gray-700 dark:text-gray-200">{{ $message->creator->name }}</span>, {{ $message['formatted_created_at'] }}
    </p>
</div>
