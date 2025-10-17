<?php
// view-pdf.php - PDF.js based viewer using the UI from test-pdf-view.html
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
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PDF Viewer</title>
    <style>
        body { font-family: Arial, sans-serif; text-align: center; background: #f8f9fa; margin:0; }
        #pdf-controls { margin: 10px; }
        button { margin: 5px; padding: 6px 12px; font-size: 14px; cursor: pointer; }
        #pdf-canvas { border: 1px solid #ccc; margin-top: 10px; background: white; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        .topbar { background: #132e51; color: #fff; padding: 12px 16px; text-align:left; }
        .topbar h4 { margin:0; font-size:16px; display:inline-block }
        .topbar .controls { float:right }
        @media (max-width:600px){ button{padding:6px 8px;font-size:13px} }
    </style>
</head>
<body>
    <div class="topbar">
        <h4>PDF Viewer</h4>
        <div class="controls">
            <button id="prev">Previous</button>
            <button id="next">Next</button>
            <button id="zoom-in">Zoom In</button>
            <button id="zoom-out">Zoom Out</button>
            <span style="margin-left:12px">Page: <span id="page-num"></span> / <span id="page-count"></span></span>
        </div>
    </div>

    <canvas id="pdf-canvas"></canvas>

    <!-- PDF.js -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js"></script>
    <script>
        const pdfUrl = <?php echo json_encode($serveUrl); ?>;
        // Worker
        if (window.pdfjsLib) {
            pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js';
        }

        let pdfDoc = null;
        let pageNum = 1;
        let scale = 1.2;
        const canvas = document.getElementById('pdf-canvas');
        const ctx = canvas.getContext('2d');

        function renderPage(num) {
            pdfDoc.getPage(num).then(function(page) {
                const viewport = page.getViewport({ scale: scale });
                canvas.height = viewport.height;
                canvas.width = viewport.width;
                const renderContext = { canvasContext: ctx, viewport: viewport };
                page.render(renderContext);
                document.getElementById('page-num').textContent = num;
            }).catch(err => {
                console.error('render error', err);
                alert('Error rendering page');
            });
        }

        // Load document
        pdfjsLib.getDocument({ url: pdfUrl }).promise.then(function(pdfDoc_) {
            pdfDoc = pdfDoc_;
            document.getElementById('page-count').textContent = pdfDoc.numPages;
            renderPage(pageNum);
        }).catch(err => {
            console.error('PDF load error', err);
            alert('Unable to load PDF.');
        });

        document.getElementById('prev').addEventListener('click', () => {
            if (!pdfDoc) return; if (pageNum <= 1) return; pageNum--; renderPage(pageNum);
        });
        document.getElementById('next').addEventListener('click', () => {
            if (!pdfDoc) return; if (pageNum >= pdfDoc.numPages) return; pageNum++; renderPage(pageNum);
        });
        document.getElementById('zoom-in').addEventListener('click', () => { scale += 0.2; renderPage(pageNum); });
        document.getElementById('zoom-out').addEventListener('click', () => { if (scale <= 0.4) return; scale -= 0.2; renderPage(pageNum); });
    </script>
</body>
</html>