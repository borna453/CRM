<div class="w-full p-6 bg-white border border-gray-200 rounded-lg shadow dark:bg-gray-800 dark:border-gray-700 mb-5">
    <a id="report-{{$report->id}}"></a>
    <h5 class="mb-2 text-2xl font-bold tracking-tight text-gray-900 dark:text-white">{{$report->title}}</h5>
    <p class="mb-3 font-normal text-gray-700 dark:text-gray-400">{!! $report->description !!}</p>
    <div class="mb-3">
        <!-- Non-image attachments-->
        <div class="flex flex-wrap">
            @foreach($report->getMedia('attachments') as $attachment)
                @if($attachment->is_document)
                    <div class="w-1/3 p-1">
                        <a href="{{ $attachment->getUrl() }}" class="block p-4 bg-gray-100 border border-gray-200 rounded hover:bg-gray-200" download="{{ $attachment->name }}">
                            Download: {{ $attachment->name }}
                        </a>
                    </div>
                @endif
            @endforeach
        </div>
        <!-- Image attachments-->
        <div class="flex flex-wrap">
            @foreach($report->getMedia('attachments') as $attachment)
                @if($attachment->is_image)
                    <div class="w-1/3 p-1">
                        <img src="{{ $attachment->getUrl() }}" alt="{{ $attachment->name }}" class="w-full cursor-pointer rounded-md"
                             @click="window.basiclightbox.create(document.getElementById('report-image-{{$attachment->id}}')).show()">
                    </div>
                @endif
            @endforeach
        </div>
    </div>
    <div class="flex justify-end items-end mt-4">
        <span class="text-sm text-gray-600 dark:text-gray-400">{{$report->appointment->dt_start->format('d-m-Y')}}</span>
    </div>
</div>
