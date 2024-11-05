@php($frameId = 'notification-preview-frame-' . $type->value)
<x-filament-forms::field-wrapper
    :id="$getId()"
    :label="$getLabel()"
    :label-sr-only="$isLabelHidden()"
    :helper-text="$getHelperText()"
    :hint="$getHint()"
    :hint-icon="$getHintIcon()"
    :required="$isRequired()"
    :state-path="$getStatePath()">
    <div>
        <iframe id="{{ $frameId }}" class="w-full min-h-[600px]" src="{{ $url }}" seamless scrolling="no" frameborder="0"></iframe>
    </div>

    <script>
        function resizeIframe(height) {
            const iframe = document.getElementById('{{ $frameId }}');
            iframe.style.height = height + 'px';
        }

        function updateFrame(iframeId, queryParam, value) {
            const iframe = document.querySelector(iframeId);
            const currentUrl = new URL(iframe.src);
            currentUrl.searchParams.set(queryParam, value);
            iframe.src = currentUrl.toString();
        }

        window.addEventListener('message', function(event) {
            if (!isNaN(event.data)) {
                resizeIframe(event.data);
            }
        });

        document.addEventListener('DOMContentLoaded', () => {
            let updateFrameTimeout;
            document.querySelector('trix-editor').addEventListener('input', function (e) {
                clearTimeout(updateFrameTimeout);
                updateFrameTimeout = setTimeout(() => {
                    @php($part = isset($context) && $context === 'feedback_form' ? 'params[email_content]' : 'email_content')
                    updateFrame('iframe#{{ $frameId }}', '{{ $part }}', this.parentElement.querySelector('input').value);
                }, 500);
            });
        });
    </script>
</x-filament-forms::field-wrapper>
