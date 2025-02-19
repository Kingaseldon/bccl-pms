<html>
    <head>
        <script src="//mozilla.github.io/pdf.js/build/pdf.js"></script>
        <script src="https://code.jquery.com/jquery-3.4.1.min.js" integrity="sha256-CSXorXvZcTkaix6Yvo6HppcZGetbYMGWSFlBw8HfCJo=" crossorigin="anonymous"></script>
        <style>
            #the-canvas {
                border: 2px solid #000;
            }
        </style>
    </head>
    <body>
        <h3>{{$name}}</h3>
        <div>
            <button id="prev">Previous</button>
            <button id="next">Next</button>
            &nbsp; &nbsp;
            <span>Page: <span id="page_num"></span> / <span id="page_count"></span></span>
        </div>
        <br/>
        <canvas id="the-canvas"></canvas>
        <script>
            <?php $fileContents = file_get_contents("$file"); $fileBase64 = base64_encode($fileContents); ?>
            var pdfData = atob("{{$fileBase64}}");
            var pageNum = 1;
            var pdfDoc;
            var pdfjsLib = window['pdfjs-dist/build/pdf'];

            // The workerSrc property shall be specified.
            pdfjsLib.GlobalWorkerOptions.workerSrc = '//mozilla.github.io/pdf.js/build/pdf.worker.js';

            var loadingTask = pdfjsLib.getDocument({
                data: pdfData
            });
            loadingTask.promise.then(function(pdf) {
                pdfDoc = pdf;
                console.log('PDF loaded');
                document.getElementById('page_count').textContent = pdf.numPages;

                // Initial/first page rendering
                renderPage(pdf, pageNum);
            });

            /**
             * Get page info from document, resize canvas accordingly, and render page.
             * @param num Page number.
             */

            function scaleCanvas(canvas, context, width, height) {
                // assume the device pixel ratio is 1 if the browser doesn't specify it
                const devicePixelRatio = window.devicePixelRatio || 1;

                // determine the 'backing store ratio' of the canvas context
                const backingStoreRatio = (
                    context.webkitBackingStorePixelRatio ||
                    context.mozBackingStorePixelRatio ||
                    context.msBackingStorePixelRatio ||
                    context.oBackingStorePixelRatio ||
                    context.backingStorePixelRatio || 1
                );

                // determine the actual ratio we want to draw at
                const ratio = devicePixelRatio / backingStoreRatio;

                if (devicePixelRatio !== backingStoreRatio) {
                    // set the 'real' canvas size to the higher width/height
                    canvas.width = width * ratio;
                    canvas.height = height * ratio;

                    // ...then scale it back down with CSS
                    canvas.style.width = width + 'px';
                    canvas.style.height = height + 'px';
                } else {
                    // this is a normal 1:1 device; just scale it simply
                    canvas.width = width;
                    canvas.height = height;
                    canvas.style.width = '';
                    canvas.style.height = '';
                }

                // scale the drawing context so everything will work at the higher ratio
                context.scale(ratio, ratio);
            }

            function renderPage(pdfDoc, num) {
                pageRendering = true;
                // Using promise to fetch the page
                pdfDoc.getPage(num).then(function(page) {
                    var desiredWidth = 900;
                    var viewport = page.getViewport({
                        scale: 1,
                    });
                    var scale = desiredWidth / viewport.width;
                    var scaledViewport = page.getViewport({
                        scale: scale,
                    });

                    //var viewport = page.getViewport({scale: 1});
                    var canvas = document.getElementById('the-canvas');
                    var ctx = canvas.getContext('2d');
                    var pageRendering = false;
                    var pageNumPending = null;
                    canvas.height = viewport.height * scale;
                    canvas.width = 900;

                    // Render PDF page into canvas context
                    var renderContext = {
                        canvasContext: ctx,
                        viewport: scaledViewport
                    };
                    var renderTask = page.render(renderContext);

                    scaleCanvas(canvas, ctx, 900, viewport.height * scale);

                    // Wait for rendering to finish
                    renderTask.promise.then(function() {
                        pageRendering = false;
                        if (pageNumPending !== null) {
                            // New page rendering is pending
                            renderPage(pageNumPending);
                            pageNumPending = null;
                        }
                    });
                });

                // Update page counters
                //document.getElementById('page_num').textContent = num;
                $("#page_num").text(num);
            }

            /**
             * If another page rendering in progress, waits until the rendering is
             * finised. Otherwise, executes rendering immediately.
             */
            function queueRenderPage(pdfDoc, num) {
                /*if (pageRendering) {
                  pageNumPending = num;
                } else {*/
                renderPage(pdfDoc, num);
                //}
            }

            /**
             * Displays previous page.
             */
            function onPrevPage() {
                if (pageNum <= 1) {
                    return;
                }
                pageNum--;
                queueRenderPage(pdfDoc, pageNum);
            }

            $(document).on("click", "#prev", function() {
                onPrevPage(pdfDoc, pageNum);
            });
            /**
             * Displays next page.
             */
            function onNextPage() {
                if (pageNum >= pdfDoc.numPages) {
                    return;
                }
                pageNum++;
                queueRenderPage(pdfDoc, pageNum);
            }
            //document.getElementById('next').addEventListener('click', onNextPage);
            $(document).on("click", "#next", function() {
                onNextPage(pdfDoc, pageNum);
            });

            $(document).ready(function() {
                $("body").on("contextmenu", function(e) {
                    return false;
                });
            });
        </script>
    </body>
</html>
