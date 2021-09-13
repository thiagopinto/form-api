<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Documento de cessão</title>
    <style>
        .conteiner {
            margin: 5% 3%;
        }

        .date {
            width: 80px;
            margin-left: auto;
            margin-right: 0;
        }

        address {
            margin-left: auto;
            margin-right: auto;
            display: block;
            font-style: italic;
        }

        .center {
            width: 50%;
            margin-left: auto;
            margin-right: auto;
        }

        .signature {
            width: max-content;
            margin-left: auto;
            margin-right: auto;
        }

        .logo-header {
            display: inline-block;
        }

        .logo-text {
            margin-left: 20px;
            display: inline-block;
        }

        .logo-header img {
            width: 100px;
        }

    </style>
</head>

<body>
    <div class="conteiner">
        <div class="date">
            <strong>{{ $today }}</strong>
        </div>
        <div class="header">
            <div class="logo-header">
                <img src="img/logo_teresina.jpg" alt="logo">
            </div>
            <div class="logo-text">
                <p>Gerência de Epidemiologia - GEEP</p>
                <p>Fundação Municipal de Saúde</p>
                <p>Núcleo de Eventos Vitais - NEV</p>
            </div>
        </div>
        <br />
        <br />
        <div class="content">
            <p>Sr(a). Diretor(a).</p>
            <br />
            <p>Em atendimento a solicitação de V.Sª, estamos enviando as fichas de {{ $type }}</p>
            <br />
            <div class="center">
                <p>Estabelecimento: {{ $form->healthUnit->alias_company_name }}</p>
                @if ($form->range_number_end != null)
                    <p>Número inicial: {{ $form->range_number_start }} até número final:
                        {{ $form->range_number_end }}.</p>
                @else
                    <p>Número: {{ $form->range_number_start }}</p>
                @endif
                @if ($form->range_number_end != null)
                    <p>Quantidade: {{ $form->range_number_end - $form->range_number_start + 1 }}</p>
                @else
                    <p>Quantidade: 1</p>
                @endif
                <p>Saldo Atual: {{ $countForms }}</p>
                <br />
                <br />
                <hr />
                <p class="signature">Solicitante: {{ $form->responsible }}</p>
                <br />
                <br />
                <br />
                <hr />
                <p class="signature">Cedente: {{ $user }}</p>

                <br/>
                <br/>
                {{ $today }}
            </div>

        </div>
        <div class="footer">
            <hr />
            <div class="center">
                <address>
                    Av. Miguel Rosa, 3860-3898 - Centro (Sul), <br />
                    Teresina - PI, 64018-560
                </address>
            </div>
        </div>
    </div>
</body>

</html>
