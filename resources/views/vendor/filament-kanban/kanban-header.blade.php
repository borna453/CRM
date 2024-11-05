@use('App\Utils\LabelColorHelper')
<h3 class="mb-2 px-4 font-semibold text-lg" style="color: {{LabelColorHelper::getLabelColorById($status['id'], '#000000')}}">
    <span>❖</span>
    {{ $status['title'] }}
</h3>
