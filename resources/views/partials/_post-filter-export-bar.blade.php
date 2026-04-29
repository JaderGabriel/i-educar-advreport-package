{{--
  Barra de emissão pós-filtro (PDF/Excel) alinhada ao padrão .ar-actions.
  Variáveis:
    - $heading (título h2 acima do card)
    - $pdfRoute, $excelRoute (URLs base das rotas de export)
    - $uid (identificador único para IDs/handlers)
    - $requiredFields (array, opcional) nomes de campos do form: 'ano', 'data_inicial', etc.
    - $requiredFieldMessages (array associativo opcional) chave => mensagem em PT
    - $cardTitle, $cardText (opcionais)
--}}
@php
  $uid = preg_replace('/[^a-z0-9_-]/i', '', (string) ($uid ?? 'export'));
  $requiredFields = $requiredFields ?? ['ano'];
  $requiredFieldMessages = $requiredFieldMessages ?? [];
@endphp
<h2 class="ar-post-filter-heading">{{ $heading ?? 'Emissão' }}</h2>
<div class="advanced-report-card" style="margin-top: 12px;">
  <strong class="advanced-report-card-title">{{ $cardTitle ?? 'Exportar' }}</strong>
  <p class="advanced-report-card-text">
    {{ $cardText ?? 'Use os filtros acima e em seguida gere o PDF (opcionalmente com gráficos, se disponível) ou exporte em Excel.' }}
  </p>
  <div class="ar-actions ar-actions--wrap">
    <div class="ar-actions__group">
      <span class="ar-actions__label">Formato</span>
      <select class="geral ar-select js-ar-postfilter-export-type-{{ $uid }}" style="width: 200px;">
        <option value="pdf">Gerar PDF</option>
        <option value="excel">Exportar Excel</option>
      </select>
      <button type="button" class="btn-green ar-btn ar-btn--secondary js-ar-postfilter-export-run-{{ $uid }}">
        <span class="ar-btn__icon ar-btn__icon--pdf" aria-hidden="true"></span>
        Executar
      </button>
    </div>
  </div>
</div>

@include('advanced-reports::partials._emit-error-modal', [
  'modalId' => 'arExportErr_' . $uid,
  'closeClass' => 'js-ar-postfilter-export-err-close-' . $uid,
  'textClass' => 'js-ar-postfilter-export-err-text-' . $uid,
])

<script>
(function () {
  const uid = @json($uid);
  const pdfBase = @json($pdfRoute ?? '');
  const excelBase = @json($excelRoute ?? '');
  const requiredFields = @json(array_values($requiredFields));
  const requiredFieldMessages = @json($requiredFieldMessages);
  const form = document.getElementById('formcadastro');
  const typeEl = document.querySelector('.js-ar-postfilter-export-type-' + uid);
  const runBtn = document.querySelector('.js-ar-postfilter-export-run-' + uid);
  const errModal = document.getElementById('arExportErr_' + uid);
  const errText = document.querySelector('.js-ar-postfilter-export-err-text-' + uid);
  const errClose = document.querySelector('.js-ar-postfilter-export-err-close-' + uid);
  if (!form || !typeEl || !runBtn || !pdfBase || !excelBase) return;

  function fieldEl(name) {
    if (name === 'ano') return document.getElementById('ano');
    return document.querySelector('[name="' + name + '"]');
  }

  function defaultMsg(name) {
    if (name === 'ano') return 'Informe o ano letivo antes de exportar.';
    if (name === 'data_inicial') return 'Informe a data inicial.';
    if (name === 'data_final') return 'Informe a data final.';
    return 'Preencha o campo obrigatório: ' + name + '.';
  }

  function validate() {
    for (let i = 0; i < requiredFields.length; i++) {
      const name = requiredFields[i];
      const el = fieldEl(name);
      const val = el && 'value' in el ? String(el.value || '').trim() : '';
      if (!val) {
        return (requiredFieldMessages && requiredFieldMessages[name]) ? requiredFieldMessages[name] : defaultMsg(name);
      }
    }
    return null;
  }

  function openErr(msg) {
    if (!errModal || !errText) {
      window.alert(msg);
      return;
    }
    errText.textContent = msg;
    errModal.style.display = 'block';
  }
  function closeErr() {
    if (!errModal) return;
    errModal.style.display = 'none';
  }
  if (errClose) errClose.addEventListener('click', function (e) { e.preventDefault(); closeErr(); });
  if (errModal) errModal.addEventListener('click', function (e) { if (e.target === errModal) closeErr(); });

  runBtn.addEventListener('click', function (e) {
    e.preventDefault();
    const msg = validate();
    if (msg) {
      openErr(msg);
      return;
    }
    const params = new URLSearchParams(new FormData(form));
    const kind = typeEl.value === 'excel' ? 'excel' : 'pdf';
    const base = kind === 'excel' ? excelBase : pdfBase;
    const sep = base.indexOf('?') >= 0 ? '&' : '?';
    window.open(base + sep + params.toString(), '_blank');
  });
})();
</script>
