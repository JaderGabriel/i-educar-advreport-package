<h1>FICHA DE MATRÍCULA</h1>
<p class="muted">Para conferência de dados e coleta de assinaturas do responsável.</p>

@include('advanced-reports::student-documents._matricula-data-box', [
  'matricula' => $matricula,
  'showInstituicao' => false,
])

<div class="box" style="margin-top: 12px; border: 2px solid #111;">
  <strong style="font-size: 14px;">AUTORIZAÇÃO DE USO DE IMAGEM E VOZ</strong>
  <p style="margin-top: 8px;">
    Eu, pai/mãe ou responsável legal pelo(a) aluno(a) identificado(a) acima, <strong>autorizo</strong> a utilização de sua
    <strong>imagem</strong> e <strong>voz</strong> em registros pedagógicos e ações de comunicação institucional (ex.: fotos, vídeos, transmissões,
    plataformas educacionais e materiais informativos), relacionados às atividades escolares.
  </p>
  <p class="muted" style="margin-top: 8px;">
    Observação: esta autorização deve ser lida com atenção pelo responsável e assinada no campo abaixo.
  </p>

  <div style="margin-top: 18px;">
    <div style="border-top: 1px solid #111; width: 420px;"></div>
    <div class="muted">Assinatura do pai/mãe/responsável</div>
  </div>
</div>

<div class="box" style="margin-top: 12px;">
  <strong>Assinaturas</strong>
  <div style="display:flex;gap:22px;flex-wrap:wrap;margin-top:12px;">
    <div style="min-width: 300px;">
      <div style="border-top: 1px solid #111; width: 320px;"></div>
      <div class="muted">Assinatura do pai/mãe/responsável</div>
    </div>
    <div style="min-width: 300px;">
      <div style="border-top: 1px solid #111; width: 320px;"></div>
      <div class="muted">Emissor do documento</div>
      <div style="margin-top: 6px;"><strong>{{ $issuerName ?? '' }}</strong></div>
      @if(!empty($schoolInep))
        <div class="muted">INEP da escola: {{ $schoolInep }}</div>
      @endif
    </div>
  </div>
</div>

