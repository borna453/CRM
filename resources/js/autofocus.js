// resources/js/focus.js

function autoFocus(element) {
    let input = element.querySelector("[autofocus='autofocus']");
    if (input) {
        setTimeout(function() {
            input.focus();
        }, 100);
    }
}

window.addEventListener("load", function(e) {
    autoFocus(document);

    var observer = new MutationObserver(function(mutations) {
        mutations.forEach(function(mutation) {
            if (mutation.addedNodes) {
                mutation.addedNodes.forEach(function(node) {
                    // Check if it's an Element node and has the x-ref attribute 'modalContainer'
                    if (node.nodeType === 1 && node.matches("[x-ref='modalContainer']")) {
                        autoFocus(node);
                    }
                });
            }
        });
    });

    observer.observe(document.body, { childList: true, subtree: true });
});
