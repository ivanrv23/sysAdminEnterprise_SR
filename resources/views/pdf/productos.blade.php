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
            bottom: -40px;
            left: 0px;
            right: 0px;
            height: 80px;

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
                        <h5 style="text-align: center"> LISTA PRODUCTOS</h5>
                    </div>
                </td>
                <td>
                    <div>
                        <h3 style="text-align: right"> {{ $company->name }}</h3>
                    </div>
                </td>
            </tr>
        </tbody>
    </table>
</header>

<footer>
    <h6>{{ $company->address }}</h6>    
</footer>

<body style="font-family: Arial, Helvetica, sans-serif">
    <br>
    <table class="border w-full" style="font-size: 12px">
        <thead>
            <tr>
                <th class="border">
                    #
                </th>
                <th class="border">
                    Almacén
                </th>
                <th class="border">
                    Producto
                </th>
                <th class="border">
                    Marca
                </th>
                <th class="border">
                    Precio Compra
                </th>
                <th class="border">
                    Precio Venta
                </th>
                <th class="border">
                    Stock
                </th>
            </tr>
        </thead>
        <tbody>
            @php
                $i = 1;
            @endphp
            @foreach ($products as $item)
                <tr style="background-color: white">
                    <td class="border" style="padding-left: 9px">{{ $i++ }}</td>
                    <td class="border" style="padding-left: 9px">{{ $item['warehouses_name'] }}</td>
                    <td class="border" style="padding-left: 9px">{{ $item['name'] }}</td>
                    <td class="border" style="padding-left: 9px">{{ $item['marks_name'] }}</td>
                    <td class="border text-right" style="padding-left: 9px">{{ $item['purchase_price'] }}</td>
                    <td class="border text-right" style="padding-left: 9px">{{ $item['sale_price'] }}</td>
                    <td class="border text-right" style="padding-left: 9px">{{ $item['stock'] }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

</body>

</html>
