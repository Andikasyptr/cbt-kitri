<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/katex@0.16.11/dist/katex.min.css">
<script defer src="https://cdn.jsdelivr.net/npm/katex@0.16.11/dist/katex.min.js"></script>
<script defer src="https://cdn.jsdelivr.net/npm/katex@0.16.11/dist/contrib/auto-render.min.js"></script>
<script>
    window.__mathQueue = [];

    window.renderMath = function (el) {
        el = el || document.body;
        if (typeof renderMathInElement !== 'function') {
            window.__mathQueue.push(el);
            return;
        }
        renderMathInElement(el, {
            delimiters: [
                { left: '$$', right: '$$', display: true },
                { left: '\\(', right: '\\)', display: false },
                { left: '$', right: '$', display: false },
            ],
            throwOnError: false,
        });
    };

    window.addEventListener('load', function () {
        const antrean = window.__mathQueue.splice(0);
        if (antrean.length === 0) antrean.push(document.body);
        antrean.forEach(function (el) { window.renderMath(el); });
    });
</script>