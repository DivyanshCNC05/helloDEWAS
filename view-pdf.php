<?php
// view-pdf.php - epaper-style PDF viewer using PDF.js
$pdf_id = $_GET['pdf_id'] ?? null;
if (!$pdf_id) {
    http_response_code(400);
    echo "Invalid request: missing pdf_id";
    exit;
}

$serveUrl = 'serve-pdf.php?pdf_id=' . urlencode($pdf_id);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>ePaper Viewer</title>
    <link rel="icon" href="https://epaper.khulasafirst.com/assets/logo-7eb930bc.png">
    <style>
        :root{--brand:#132e51;--muted:#6c757d;--bg:#f4f6f8}
        *{box-sizing:border-box}
        body{margin:0;font-family:Arial,Helvetica,sans-serif;background:var(--bg);color:#222}
        /* Header */
        .header{background:var(--brand);color:#fff;padding:10px 16px;display:flex;align-items:center;gap:12px}
        .header img{height:40px}
        .header .title{font-size:18px;font-weight:600}
        .header .meta{margin-left:auto;font-size:13px;color:rgba(255,255,255,0.85)}

        /* Viewer layout */
        .viewer-wrap{display:flex;gap:12px;padding:12px}
        .viewer-main{flex:1;background:#fff;border-radius:6px;box-shadow:0 2px 8px rgba(0,0,0,0.08);position:relative;overflow:hidden}
        .viewer-sidebar{width:140px;max-width:30%;overflow:auto}

        /* toolbar */
        .toolbar{display:flex;align-items:center;gap:8px;padding:10px;border-bottom:1px solid #eee}
        .toolbar .btn{background:#fff;border:1px solid #ddd;padding:6px 10px;border-radius:4px;cursor:pointer}
        .toolbar .btn:active{transform:translateY(1px)}
        .toolbar .page-info{margin-left:auto;color:var(--muted)}

        /* canvas container */
        .canvas-holder{display:flex;justify-content:center;padding:12px}
        #pdf-canvas{max-width:100%;height:auto;display:block;border-radius:2px}

        /* overlay nav */
        .nav-overlay{position:absolute;top:50%;left:0;right:0;display:flex;justify-content:space-between;pointer-events:none}
        .nav-overlay button{pointer-events:auto;background:rgba(0,0,0,0.5);color:#fff;border:0;padding:12px;border-radius:50%;margin:0 8px;cursor:pointer}

        /* thumbnails */
        .thumb{padding:6px;background:#fff;border-radius:6px;margin-bottom:8px;cursor:pointer;border:1px solid #e6e6e6}
        .thumb canvas{display:block;width:100%;height:auto}
        .thumb.active{outline:3px solid var(--brand)}

        /* responsive */
        @media(max-width:900px){
            .viewer-wrap{flex-direction:column}
            .viewer-sidebar{width:100%;display:flex;gap:8px;overflow-x:auto;padding:8px}
            .thumb{min-width:90px;margin-right:8px}
        }
    </style>
    <!-- PDF.js -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js"></script>
    <script>if(window.pdfjsLib) pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js';</script>
</head>
<body>
    <header class="header">
        <img src="https://epaper.khulasafirst.com/assets/logo-7eb930bc.png" alt="logo">
        <div class="title">ePaper Viewer</div>
        <div class="meta">Open: <?php echo htmlspecialchars($pdf_id); ?></div>
    </header>

    <div class="viewer-wrap">
        <main class="viewer-main">
            <div class="toolbar">
                <button class="btn" id="prev">&#9664; Prev</button>
                <button class="btn" id="next">Next &#9654;</button>
                <button class="btn" id="zoom-out">-</button>
                <button class="btn" id="zoom-in">+</button>
                <button class="btn" id="fit-width">Fit Width</button>
                <a class="btn" id="download" href="<?php echo htmlspecialchars($serveUrl); ?>" download>Download</a>
                <div class="page-info">Page <span id="page-num">1</span> / <span id="page-count">0</span></div>
            </div>

            <div class="canvas-holder" id="canvas-holder">
                <canvas id="pdf-canvas"></canvas>
            </div>

            <div class="nav-overlay">
                <div style="display:flex;align-items:center;">
                    <button id="left-nav" title="Previous page">&#9664;</button>
                </div>
                <div style="display:flex;align-items:center;">
                    <button id="right-nav" title="Next page">&#9654;</button>
                </div>
            </div>
        </main>

        <aside class="viewer-sidebar" id="thumbnails"></aside>
    </div>

    <script>
        const pdfUrl = <?php echo json_encode($serveUrl); ?>;
        let pdfDoc = null;
        let pageNum = 1;
        let scale = 1.0; // base scale; fit-width will compute dynamically
        const canvas = document.getElementById('pdf-canvas');
        const ctx = canvas.getContext('2d');
        const pageNumEl = document.getElementById('page-num');
        const pageCountEl = document.getElementById('page-count');
        const thumbnailsEl = document.getElementById('thumbnails');

        function renderPage(num, fitToWidth=false) {
            pdfDoc.getPage(num).then(page=>{
                const unscaledViewport = page.getViewport({scale:1});
                if (fitToWidth) {
                    const holder = document.getElementById('canvas-holder');
                    const maxW = Math.max(300, holder.clientWidth - 24); // padding
                    scale = maxW / unscaledViewport.width;
                }
                const viewport = page.getViewport({scale: scale});
                canvas.width = Math.round(viewport.width);
                canvas.height = Math.round(viewport.height);
                // scale canvas CSS to fit horizontally
                canvas.style.width = Math.min(100, (viewport.width / canvas.width) * 100) + '%';
                const renderContext = { canvasContext: ctx, viewport };
                page.render(renderContext).promise.then(()=>{
                    pageNumEl.textContent = num;
                });
            }).catch(err=>{console.error('render error',err); alert('Unable to render page');});
        }

        function renderThumbnails() {
            thumbnailsEl.innerHTML = '';
            for (let i=1;i<=pdfDoc.numPages;i++) {
                createThumb(i);
            }
        }

        function createThumb(pageNumber){
            pdfDoc.getPage(pageNumber).then(page=>{
                const viewport = page.getViewport({scale:0.15});
                const tcanvas = document.createElement('canvas');
                tcanvas.width = Math.round(viewport.width);
                tcanvas.height = Math.round(viewport.height);
                const tctx = tcanvas.getContext('2d');
                page.render({canvasContext:tctx,viewport}).promise.then(()=>{
                    const wrap = document.createElement('div');
                    wrap.className = 'thumb';
                    if (pageNumber === pageNum) wrap.classList.add('active');
                    wrap.appendChild(tcanvas);
                    wrap.addEventListener('click', ()=>{ pageNum = pageNumber; renderPage(pageNum,true); highlightThumb(pageNumber); window.scrollTo({top:0,behavior:'smooth'}); });
                    thumbnailsEl.appendChild(wrap);
                });
            });
        }

        function highlightThumb(n){
            const thumbs = thumbnailsEl.querySelectorAll('.thumb');
            thumbs.forEach((t,idx)=>{ t.classList.toggle('active', idx+1===n); });
        }

        // Load PDF
        pdfjsLib.getDocument({url: pdfUrl}).promise.then(pdf=>{
            pdfDoc = pdf;
            pageCountEl.textContent = pdfDoc.numPages;
            renderPage(pageNum, true);
            renderThumbnails();
        }).catch(err=>{console.error('load error',err); alert('Failed to load PDF');});

        // Controls
        document.getElementById('prev').addEventListener('click', ()=>{ if(!pdfDoc) return; if(pageNum<=1) return; pageNum--; renderPage(pageNum,true); highlightThumb(pageNum); });
        document.getElementById('next').addEventListener('click', ()=>{ if(!pdfDoc) return; if(pageNum>=pdfDoc.numPages) return; pageNum++; renderPage(pageNum,true); highlightThumb(pageNum); });
        document.getElementById('left-nav').addEventListener('click', ()=>{ document.getElementById('prev').click(); });
        document.getElementById('right-nav').addEventListener('click', ()=>{ document.getElementById('next').click(); });
        document.getElementById('zoom-in').addEventListener('click', ()=>{ scale = Math.min(4, scale + 0.25); renderPage(pageNum); });
        document.getElementById('zoom-out').addEventListener('click', ()=>{ scale = Math.max(0.25, scale - 0.25); renderPage(pageNum); });
        document.getElementById('zoom-in').id = 'zoom-in'; // ensure id present
        document.getElementById('zoom-out').id = 'zoom-out';
        document.getElementById('fit-width').addEventListener('click', ()=>{ renderPage(pageNum, true); });

        // Keyboard navigation
        window.addEventListener('keydown', (e)=>{
            if (e.key === 'ArrowLeft') document.getElementById('prev').click();
            if (e.key === 'ArrowRight') document.getElementById('next').click();
            if (e.key === '+') document.getElementById('zoom-in').click();
            if (e.key === '-') document.getElementById('zoom-out').click();
        });
    </script>
</body>
</html>