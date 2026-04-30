{{--
  Termo resumido de autorização de imagem/voz (com assinatura do responsável nesta folha)
  e rol de documentos comuns à matrícula (referência para conferência no ato da matrícula).
  Caixas vazias para marcação manual (X) pelo servidor após impressão.
--}}
@php
  $itensDocumentacaoMatricula = [
    'Certidão de nascimento / registro civil (ou documento equivalente da identidade civil)',
    'RG e CPF do(a) estudante (quando exigidos para a idade / rede)',
    'CPF do(a) responsável legal',
    'Documento de identificação com foto do(a) responsável legal (RG, CNH ou equivalente)',
    'Comprovante de residência atualizado (em nome do responsável ou com declaração, conforme regra da rede)',
    'Cartão Nacional de Saúde (CNS) / vacinação ou declaração de saúde (conforme exigência local)',
    'NIS (PIS/PASEP) ou comprovante de Cadastro Único (quando exigido)',
    'Histórico escolar, boletim ou declaração/guia de transferência (alunos vindos de outra escola)',
    'Declaração de conclusão ou comprovante de série/ano anterior (quando aplicável)',
    'Fotografia 3x4 ou arquivo digital do(a) estudante (conforme regra da unidade)',
    'Laudo ou relatório médico / psicopedagógico (educação especial / atendimento educacional especializado, quando aplicável)',
    'Demais documentos exigidos por calendário ou resolução local (anotar ao lado): __________________________',
  ];

  $nDoc = count($itensDocumentacaoMatricula);
  $numColsDoc = 3;
  $linhasDoc = (int) ceil($nDoc / $numColsDoc);
@endphp

<div class="box" style="margin-top: 8px; border: 1px solid #111827;">
  <strong style="font-size: 10px;">AUTORIZAÇÃO DE USO DE IMAGEM E VOZ (TEXTO RESUMIDO)</strong>
  <p style="margin-top: 6px; text-align: justify; font-size: 9px; line-height: 1.38;">
    Declaro estar ciente de que, <strong>mediante minha assinatura neste quadro</strong>, <strong>autorizo</strong> a instituição de ensino a utilizar
    a <strong>imagem</strong> e a <strong>voz</strong> do(a) estudante identificado(a) nesta ficha em registros pedagógicos e em ações de comunicação
    institucional relacionadas às atividades escolares, nos termos da legislação aplicável e das normas da rede.
    O termo detalhado pode ser emitido em <strong>Fichas → Termo de Autorização</strong>, quando a rede exigir arquivo separado.
  </p>
  <div style="margin-top: 12px; text-align: center;">
    <div style="border-top: 1px solid #111827; max-width: 420px; margin: 0 auto;"></div>
    <div class="muted" style="margin-top: 4px; font-size: 8px;">Assinatura do(a) pai/mãe/responsável legal</div>
    @if(!empty($matricula->responsavel_exibicao))
      <div style="margin-top: 4px; font-size: 10px;"><strong>{{ $matricula->responsavel_exibicao }}</strong></div>
    @endif
  </div>
</div>

<div class="box" style="margin-top: 8px;">
  <strong style="font-size: 10px;">Documentação apresentada no ato da matrícula</strong>
  <p class="muted" style="margin-top: 3px; font-size: 8px; margin-bottom: 6px; line-height: 1.3;">
    Itens de referência (conferir portaria/calendário local). Marcar com <strong>X</strong> o que foi entregue.
  </p>
  <table style="width: 100%; border-collapse: collapse; font-size: 8px; table-layout: fixed;">
    @for($r = 0; $r < $linhasDoc; $r++)
      <tr>
        @for($c = 0; $c < $numColsDoc; $c++)
          @php($idx = $r + $c * $linhasDoc)
          <td style="width: 3%; vertical-align: top; padding: 2px 3px 2px 0;">
            @if($idx < $nDoc)
              <span style="display: inline-block; width: 10px; height: 10px; border: 1px solid #111827; vertical-align: middle;" title="Marcar com X"></span>
            @endif
          </td>
          <td style="width: 30%; vertical-align: top; padding: 2px 6px 2px 0; text-align: justify;">
            {{ ($idx < $nDoc) ? $itensDocumentacaoMatricula[$idx] : '' }}
          </td>
        @endfor
      </tr>
    @endfor
  </table>
</div>
