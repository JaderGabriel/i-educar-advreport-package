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

  $metade = (int) ceil(count($itensDocumentacaoMatricula) / 2);
  $colEsquerda = array_slice($itensDocumentacaoMatricula, 0, $metade);
  $colDireita = array_slice($itensDocumentacaoMatricula, $metade);
  $linhas = count($colEsquerda);
@endphp

<div class="box" style="margin-top: 12px; border: 1px solid #111827;">
  <strong style="font-size: 11px;">AUTORIZAÇÃO DE USO DE IMAGEM E VOZ (TEXTO RESUMIDO)</strong>
  <p style="margin-top: 8px; text-align: justify; font-size: 10px; line-height: 1.45;">
    Declaro estar ciente de que, <strong>mediante minha assinatura neste quadro</strong>, <strong>autorizo</strong> a instituição de ensino a utilizar
    a <strong>imagem</strong> e a <strong>voz</strong> do(a) estudante identificado(a) nesta ficha em registros pedagógicos e em ações de comunicação
    institucional relacionadas às atividades escolares, nos termos da legislação aplicável e das normas da rede.
    O termo detalhado pode ser emitido em <strong>Fichas → Termo de Autorização</strong>, quando a rede exigir arquivo separado.
  </p>
  <div style="margin-top: 18px; text-align: center;">
    <div style="border-top: 1px solid #111827; max-width: 420px; margin: 0 auto;"></div>
    <div class="muted" style="margin-top: 6px; font-size: 9px;">Assinatura do(a) pai/mãe/responsável legal</div>
    @if(!empty($matricula->responsavel_exibicao))
      <div style="margin-top: 4px; font-size: 10px;"><strong>{{ $matricula->responsavel_exibicao }}</strong></div>
    @endif
  </div>
</div>

<div class="box" style="margin-top: 12px;">
  <strong>Documentação apresentada no ato da matrícula</strong>
  <p class="muted" style="margin-top: 4px; font-size: 9px; margin-bottom: 10px;">
    Itens de referência para matrícula na rede pública (conferir portaria/calendário local). O servidor marca com <strong>X</strong> na caixa, à mão, o que foi entregue em original ou cópia autenticada/conferida.
  </p>
  <table style="width: 100%; border-collapse: collapse; font-size: 9px;">
    @for($i = 0; $i < $linhas; $i++)
      <tr>
        <td style="width: 4%; vertical-align: top; padding: 5px 4px 5px 0;">
          <span style="display: inline-block; width: 12px; height: 12px; border: 2px solid #111827; vertical-align: middle;" title="Marcar com X"></span>
        </td>
        <td style="width: 46%; vertical-align: top; padding: 5px 8px 5px 0; text-align: justify;">
          {{ $colEsquerda[$i] ?? '' }}
        </td>
        <td style="width: 4%; vertical-align: top; padding: 5px 4px 5px 0;">
          @if(isset($colDireita[$i]))
            <span style="display: inline-block; width: 12px; height: 12px; border: 2px solid #111827; vertical-align: middle;" title="Marcar com X"></span>
          @endif
        </td>
        <td style="width: 46%; vertical-align: top; padding: 5px 0 5px 0; text-align: justify;">
          {{ $colDireita[$i] ?? '' }}
        </td>
      </tr>
    @endfor
  </table>
</div>
