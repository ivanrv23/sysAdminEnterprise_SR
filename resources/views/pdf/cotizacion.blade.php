<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice</title>
    <style>
        .border {
            border: 1px solid black;
            border-collapse: collapse;
        }

        .w-full {
            width: 100%;
        }

        .text-center {
            text-align: center;
        }

        .text-right {
            text-align: right;
        }

        thead {
            background: #cccccc;
        }

        .contenedor {
            text-align: right align-content: right margin: 0
        }

        .contenedor2 {
            text-align: left;
            align-content: left;
            margin: 0
        }

        footer {
            position: fixed;
            bottom: -30px;
            left: 0px;
            right: 0px;
            height: 50px;

            /** Extra personal styles **/
            text-align: center;
            line-height: 35px;
        }
    </style>
</head>

<header>

    <table class="w-full">
        <tbody>
            <tr>
                <td>
                    <div>
                        <img width="90" src="{{ public_path($customizer->logo) }}" alt="Logo Empresa">
                    </div>
                </td>
                <td>
                    <div>
                        <h2 style="text-align: right"> {{ $company->abbreviation }}</h2>
                    </div>
                </td>
            </tr>
        </tbody>
    </table>
</header>

<footer>
    {{ $company->address }}
</footer>

<body style="font-family: Arial, Helvetica, sans-serif">
    <table class="w-full">
        <tbody>
            <tr>
                <td style="font-size: 18px">
                    <p><strong>Empresa:</strong> {{ $company->name }} <br> <strong>Teléfonos:</strong>
                        {{ $company->phone }}</p>
                    @php
                        if ($account != null) {
                            echo '<p><strong>DATOS CUENTA:</strong> <br><strong>Entidad Bancaria: </strong>' .
                                $account->banking_entity .
                                '<br> <strong>N°. Cuenta: </strong>' .
                                $account->account_number .
                                '<br>
                        <strong>CCI: </strong>' .
                                $account->cci .
                                '</p>';
                        }
                    @endphp
                    <p><strong>Cliente:</strong> {{ $customers_name }}</p>
                </td>
                <td>
                    <table class="border w-full">
                        <tr>
                            <td class="text-center">
                                <h3>COTIZACION</h3>
                            </td>
                        </tr>
                        <tr>
                            <td class="text-center">No. {{ $quotation->voucher_number }}</td>
                        </tr>
                        <tr>
                            <td class="text-center">Fecha: <?php echo date('d-m-Y', strtotime($quotation->date)); ?></td>
                        </tr>
                    </table>
                </td>
            </tr>
        </tbody>
    </table>

    <br>

    <table class="border w-full" style="font-size: 18px">
        <thead>
            <tr>
                <th class="border">
                    #
                </th>
                <th class="border">
                    Cantidad
                </th>
                <th class="border">
                    Descripción
                </th>
                <th class="border">
                    Precio
                </th>
                <th class="border">
                    Total
                </th>
            </tr>
        </thead>
        <tbody>
            @php
                $i = 1;
            @endphp
            @foreach ($quotation_details as $item)
                <tr>
                    <td class="border" style="padding-left: 10px">{{ $i++ }}</td>
                    <td class="border" style="padding-left: 10px">{{ $item['quantity'] }}</td>
                    <td class="border" style="padding-left: 10px">{{ $item['product'] }} - {{ $item['mark_name'] }}
                    </td>
                    <td class="border text-right" style="padding-left: 10px">{{ $item['price'] }}</td>
                    <td class="border text-right" style="padding-left: 10px">{{ $item['subTotal'] }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    {{-- Calculando total- --}}
    @php
        if ($quotation->igv == 'Más IGV') {
            foreach ($quotation_details as $item) {
                $total += $item['quantity'] * $item['price'];
                $igv += $item['igv'];
            }
            $subTot = $total;
        } else {
            foreach ($quotation_details as $item) {
                $total += $item['quantity'] * $item['price'];
                $igv += $item['igv'];
            }
            $subTot = $total - $igv;
        }
    @endphp

    <br>
    <table class="border" style="width: auto; float: right;">
        <thead>
            <tr>
                <th style="font-size: 20px" class="border">Sub Total</th>
                <td style="font-size: 20px; background-color:white" class="border text-center">
                    {{ number_format($subTot, 2, '.', ',') }}</td>
            </tr>

            <tr>
                <th style="font-size: 20px" class="border">IGV</th>
                <td style="font-size: 20px; background-color:white" class="border text-center">
                    {{ number_format($igv, 2, '.', ',') }}</td>
            </tr>

            <tr>
                <th style="font-size: 20px" class="border">Total</th>
                <td style="font-size: 20px; background-color:white" class="border text-center">
                    {{ number_format($subTot + $igv, 2, '.', ',') }}</td>
            </tr>
        </thead>
        </tbody>
    </table>

</body>

</html>
