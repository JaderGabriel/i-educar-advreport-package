<h1>TERMO DE AUTORIZAÇÃO DE USO DE IMAGEM E VOZ</h1>
<p class="muted">Documento complementar à matrícula, conforme política da rede/escola.</p>

@include('advanced-reports::student-documents._matricula-data-box', [
  'matricula' => $matricula,
  'showInstituicao' => false,
])

<div class="box" style="margin-top: 12px;">
  <p style="text-align: justify; line-height: 1.45;">
    Eu, <strong>pai, mãe ou responsável legal</strong> pelo(a) estudante identificado(a) acima, declaro ter ciência e,
    na medida permitida pela legislação vigente e pelas normas da instituição,
    <strong>autorizo</strong> o uso da <strong>imagem</strong> (fotos, filmagens, transmissões) e da <strong>voz</strong> do(a) referido(a) estudante
    em atividades pedagógicas e em ações de comunicação institucional relacionadas ao processo de ensino-aprendizagem
    (ex.: registros de aula, eventos escolares, portais, redes sociais oficiais da rede/escola e materiais informativos),
    respeitados os ditames legais sobre proteção de dados e direitos da criança e do adolescente.
  </p>
  <p class="muted" style="margin-top: 10px; font-size: 10px;">
    Esta autorização não dispensa a observância de boas práticas de privacidade nem impede o exercício de direitos previstos em lei,
    conforme orientação da rede de ensino.
  </p>
</div>

@include('advanced-reports::student-forms._signatures-responsavel-emissor', [
  'issuerName' => $issuerName ?? null,
  'schoolInep' => $schoolInep ?? null,
  'responsavelExibicao' => $matricula->responsavel_exibicao ?? null,
])
