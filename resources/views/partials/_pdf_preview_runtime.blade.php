@once
  @push('scripts')
    <script>
      (function () {
        if (window.AdvancedReportsPdfPreview) {
          return;
        }

        var PDFJS_VERSION = '3.11.174';
        var PDFJS_BASE = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/' + PDFJS_VERSION + '/';

        function loadScript(src) {
          return new Promise(function (resolve, reject) {
            var s = document.createElement('script');
            s.src = src;
            s.async = true;
            s.onload = function () { resolve(); };
            s.onerror = function () { reject(new Error('Falha ao carregar ' + src)); };
            document.head.appendChild(s);
          });
        }

        function destroyRoot(root) {
          if (!root) {
            return;
          }
          if (root.__arPdfDestroy) {
            try {
              root.__arPdfDestroy();
            } catch (e) {
              /* ignore */
            }
            root.__arPdfDestroy = null;
          }
          root.innerHTML = '';
        }

        async function ensurePdfJs() {
          if (window.pdfjsLib) {
            return window.pdfjsLib;
          }
          await loadScript(PDFJS_BASE + 'pdf.min.js');
          if (!window.pdfjsLib) {
            throw new Error('pdfjsLib indisponível.');
          }
          window.pdfjsLib.GlobalWorkerOptions.workerSrc = PDFJS_BASE + 'pdf.worker.min.js';

          return window.pdfjsLib;
        }

        window.AdvancedReportsPdfPreview = {
          maxPages: 30,

          open: async function (root, url) {
            if (!root) {
              return;
            }

            root.__arPdfToken = (root.__arPdfToken || 0) + 1;
            var token = root.__arPdfToken;

            destroyRoot(root);

            var loading = document.createElement('div');
            loading.className = 'ar-pdf-preview-status';
            loading.textContent = 'Carregando prévia…';
            root.appendChild(loading);

            root.oncontextmenu = function (e) {
              e.preventDefault();
            };

            try {
              var pdfjsLib = await ensurePdfJs();
              var loadingTask = pdfjsLib.getDocument({ url: url, withCredentials: true });
              var pdf = await loadingTask.promise;

              if (token !== root.__arPdfToken) {
                try {
                  pdf.destroy();
                } catch (e2) {
                  /* ignore */
                }

                return;
              }

              root.__arPdfDestroy = function () {
                try {
                  pdf.destroy();
                } catch (e3) {
                  /* ignore */
                }
              };

              loading.remove();

              var wrap = document.createElement('div');
              wrap.className = 'ar-pdf-preview-pages';

              var max = Math.min(pdf.numPages, this.maxPages);
              if (pdf.numPages > this.maxPages) {
                var note = document.createElement('div');
                note.className = 'ar-pdf-preview-note';
                note.textContent = 'Prévia limitada às primeiras ' + this.maxPages + ' páginas.';
                wrap.appendChild(note);
              }

              for (var p = 1; p <= max; p++) {
                if (token !== root.__arPdfToken) {
                  try {
                    pdf.destroy();
                  } catch (e4) {
                    /* ignore */
                  }
                  return;
                }

                var page = await pdf.getPage(p);
                var scale = 1.12;
                var viewport = page.getViewport({ scale: scale });
                var canvas = document.createElement('canvas');
                canvas.className = 'ar-pdf-preview-page';
                var ctx = canvas.getContext('2d', { alpha: false });
                if (!ctx) {
                  throw new Error('Canvas 2D indisponível.');
                }
                canvas.width = viewport.width;
                canvas.height = viewport.height;

                var pageWrap = document.createElement('div');
                pageWrap.className = 'ar-pdf-preview-pageWrap';
                pageWrap.appendChild(canvas);
                wrap.appendChild(pageWrap);

                await page.render({ canvasContext: ctx, viewport: viewport }).promise;
              }

              root.appendChild(wrap);
            } catch (err) {
              if (token !== root.__arPdfToken) {
                return;
              }
              destroyRoot(root);
              var errEl = document.createElement('div');
              errEl.className = 'ar-pdf-preview-status ar-pdf-preview-status--error';
              errEl.textContent = 'Não foi possível carregar a prévia (rede/CSP). Use a emissão final em nova aba.';
              root.appendChild(errEl);
              if (window.console && console.error) {
                console.error(err);
              }
            }
          },

          close: function (root) {
            if (!root) {
              return;
            }
            root.__arPdfToken = (root.__arPdfToken || 0) + 1;
            destroyRoot(root);
            root.oncontextmenu = null;
          },
        };
      })();
    </script>
  @endpush
@endonce
