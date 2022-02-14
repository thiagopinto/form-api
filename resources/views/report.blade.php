<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Relatório de acompanhamento</title>
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

        table,
        th,
        td {
            border: 1px solid black;
            border-collapse: collapse;
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
            <p>Relatório de Acompanhamento</p>
            <br />
            <br />
            <div class="">
                <table class="table">
                    <thead>
                        <tr>
                            <th>CNES</th>
                            <th>Nome</th>
                            <th>Recebidos</th>
                            <th>Utilizados</th>
                            <th>Cancelados</th>
                            <th>Estoque</th>
                            <th>Último recebimento</th>
                            <th>Dias Último recebimento</th>
                        </tr>
                    </thead>
                    <tbody>

                        @foreach ($forms as $form)
                            <tr>
                                <td>{{ $form->cnes_code }}</td>
                                <td>{{ $form->name }}</td>
                                <td>{{ $form->received }}</td>
                                <td>{{ $form->used }}</td>
                                <td>{{ $form->canceled }}</td>
                                <td>{{ $form->stock }}</td>
                                <td>{{ $form->last_receipt }}</td>
                                <td>{{ $form->last_receipt_day }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

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
