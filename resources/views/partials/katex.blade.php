<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/katex@0.16.11/dist/katex.min.css">
<script defer src="https://cdn.jsdelivr.net/npm/katex@0.16.11/dist/katex.min.js"></script>
<script defer src="https://cdn.jsdelivr.net/npm/katex@0.16.11/dist/contrib/auto-render.min.js"></script>
<script>
    // Antrean: permintaan render sebelum KaTeX siap disimpan dulu
    window.__mathQueue = [];

    // Ubah teks berformat $...$ menjadi rumus di dalam elemen tertentu
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

    // Event "load" terjadi setelah semua script defer selesai dimuat
    window.addEventListener('load', function () {
        const antrean = window.__mathQueue.splice(0);
        if (antrean.length === 0) antrean.push(document.body);
        antrean.forEach(function (el) { window.renderMath(el); });
    });
</script>