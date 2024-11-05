<x-mail::message :preview="$preview ?? false" :type="$type ?? null">
    <x-slot:raw>{!! $email_content !!}</x-slot:raw>
</x-mail::message>

<script>
    function sendHeight() {
        var height = document.documentElement.scrollHeight;
        parent.postMessage(height, '*');
    }

    window.onload = sendHeight;
    window.onresize = sendHeight;
</script>
