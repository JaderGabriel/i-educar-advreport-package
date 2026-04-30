{{-- INEP (pessoa) e matrícula; em documentos de aluno pode exibir matrícula interna (i-Educar) no lugar da matrícula funcional. --}}
@php
  $issuerMatriculaInternaAluno = $issuerMatriculaInternaAluno ?? null;
@endphp
@if(!empty($issuerMatriculaInternaAluno))
  <div class="muted issuer-person-matricula" style="font-size: 10px; margin-top: 2px;">
    Matrícula interna (i-Educar): {{ $issuerMatriculaInternaAluno }}
  </div>
@else
  @php
    $svc = app(\iEducar\Packages\AdvancedReports\Services\IssuerSignatureDetails::class);
    $base = $svc->forAuthenticatedUser();
    $issuerPersonInep = isset($issuerPersonInep) ? $issuerPersonInep : $base['issuerPersonInep'];
    $issuerMatriculaFuncional = isset($issuerMatriculaFuncional) ? $issuerMatriculaFuncional : $base['issuerMatriculaFuncional'];
    $issuerPersonIdpes = isset($issuerPersonIdpes) ? $issuerPersonIdpes : $base['issuerPersonIdpes'];
    if ($issuerPersonIdpes !== null) {
        $issuerPersonIdpes = (int) $issuerPersonIdpes;
    }
  @endphp
  @if(!empty($issuerPersonInep))
    <div class="muted issuer-person-inep" style="font-size: 10px; margin-top: 2px;">
      INEP: {{ $issuerPersonInep }}
    </div>
  @endif
  @if(!empty($issuerMatriculaFuncional) || !empty($issuerPersonIdpes))
    <div class="muted issuer-person-matricula" style="font-size: 10px; margin-top: 2px;">
      MATRÍCULA:
      @if(!empty($issuerMatriculaFuncional))
        {{ $issuerMatriculaFuncional }}
      @endif
      @if(!empty($issuerMatriculaFuncional) && !empty($issuerPersonIdpes))
        /
      @endif
      @if(!empty($issuerPersonIdpes))
        {{ $issuerPersonIdpes }}
      @endif
    </div>
  @endif
@endif
