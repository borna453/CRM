@if ($loginAllowed)
    <span class="font-semibold">Login Allowed:</span>
    <x-heroicon-s-check class="text-green-500 inline-block h-5 w-5" />
@else
    <span class="font-semibold">Login Allowed:</span>
    <x-heroicon-s-x-mark class="text-red-500 inline-block h-5 w-5" />
@endif
