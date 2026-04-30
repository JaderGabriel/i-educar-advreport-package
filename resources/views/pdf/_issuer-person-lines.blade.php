{{-- INEP (pessoa) e MATRÍCULA (funcional / idpes); overrides opcionais vêm do include pai --}}
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
