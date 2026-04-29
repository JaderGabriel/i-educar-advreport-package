<!doctype html>
<html lang="pt-br">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Validação de documento</title>
  <style>
    body { font-family: Arial, sans-serif; margin: 24px; color: #111827; }
    .card { border: 1px solid #e5e7eb; border-radius: 10px; padding: 16px; background: #fff; max-width: 980px; }
    .muted { color: #6b7280; }
    h1 { margin: 0 0 12px; }
    h2 { margin-top: 18px; }
    table { width: 100%; border-collapse: collapse; margin-top: 10px; }
    th, td { border: 1px solid #e5e7eb; padding: 8px; vertical-align: top; text-align: left; }
    th { background: #f9fafb; }
    pre { white-space: pre-wrap; margin: 0; }
    .badge-ok { display:inline-block; padding: 2px 8px; border-radius: 999px; background: #dcfce7; color: #166534; font-weight: 700; }
    .badge-bad { display:inline-block; padding: 2px 8px; border-radius: 999px; background: #fee2e2; color: #991b1b; font-weight: 700; }
    .row { display:flex; flex-wrap:wrap; gap: 10px; }
    .row > div { min-width: 220px; }
    .code { font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace; }
  </style>
</head>
<body>
  <h1>Validação de documento</h1>

  @if(!$doc)
    <div class="card">
      <div class="badge-bad">NÃO ENCONTRADO</div>
      <p>O código <strong class="code">{{ $code }}</strong> não foi localizado.</p>
      <p class="muted">Confirme se o QR Code/código foi digitado corretamente e tente novamente.</p>
    </div>
  @else
    <div class="card">
      @if(!empty($isValid))
        <div class="badge-ok">VÁLIDO</div>
      @else
        <div class="badge-bad">INVÁLIDO</div>
      @endif

      <p class="muted" style="margin: 10px 0 0; max-width: 980px;">
        @if(!empty($isValid))
          <strong>O que isso significa?</strong> O documento foi localizado e a assinatura de verificação confere com o conteúdo registrado no momento da emissão.
          Em outras palavras, o PDF corresponde a um documento emitido pelo sistema, com este código.
        @else
          <strong>O que isso significa?</strong> O documento foi localizado, porém a assinatura de verificação <strong>não confere</strong> com o conteúdo registrado.
          Isso pode ocorrer por alteração do conteúdo/registro, chave de assinatura diferente entre ambientes, ou inconsistência de dados. Em caso de dúvida, solicite nova emissão.
        @endif
      </p>
      <div class="row" style="margin-top: 10px;">
        <div><strong>Código</strong>: <span class="code">{{ $doc->code }}</span></div>
        <div><strong>Tipo</strong>: {{ (string) (($summary['Tipo'] ?? '') ?: $doc->type) }}</div>
        <div><strong>Emitido em</strong>: {{ optional($doc->issued_at)->format('d/m/Y H:i') }}</div>
      </div>
    </div>

    <h2>Resumo oficial</h2>
    <table>
      <thead>
      <tr><th>Campo</th><th>Valor</th></tr>
      </thead>
      <tbody>
      @foreach(($summary ?? []) as $k => $v)
        <tr>
          <td>{{ (string) $k }}</td>
          <td>
            {{ is_bool($v) ? ($v ? 'Sim' : 'Não') : (string) $v }}
          </td>
        </tr>
      @endforeach
      </tbody>
    </table>

    <p class="muted" style="max-width: 980px; margin-top: 10px;">
      Observação (LGPD): esta página exibe apenas um resumo de verificação. Dados completos do documento não são
      exibidos publicamente.
    </p>
  @endif
</body>
</html>
