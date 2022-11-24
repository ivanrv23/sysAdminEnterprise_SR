<?php

namespace App\Http\Controllers;

use App\Models\AccountReceivable;
use App\Models\AccountReceivableDetail;
use App\Models\CashRegister;
use App\Models\Coin;
use App\Models\Company;
use App\Models\Customer;
use App\Models\Customizer;
use App\Models\Document;
use App\Models\Mark;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\PaymentMethod;
use App\Models\Product;
use App\Models\ProofPayment;
use App\Models\Provider;
use App\Models\Purchase;
use App\Models\PurchaseDetail;
use App\Models\Warehouse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Date;
use Inertia\Inertia;
use PhpParser\Node\Expr\Cast\Object_;

class ReportController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index($consultaCaja = null, $dateInicio = null, $dateFin = null)
    {

        $company = Auth::user()->companies_id;
        // 
        if ($consultaCaja == null) {
            $cja = 1;
        } else {
            $cja = $consultaCaja;
        }
        // 
        if ($dateInicio == null) {
            $DateAndTimeInicio = date('Y-m-d');
        } else {
            $DateAndTimeInicio = $dateInicio;
        }
        // 
        if ($dateFin == null) {
            $DateAndTimeFin = date('Y-m-d');
        } else {
            $DateAndTimeFin = $dateFin;
        }
        // VENTAS TOTALES PEN - USD
        $totalVentasDiaSoles = 0;
        $totalVentasDiaDolares = 0;
        $totalVentasDolares = 0;
        $total_pen_usd = 0;

        $total_pen = Order::where('companies_id', $company)->where('cash_registers_id', $cja)->where('proof_payments_id', '!=', 4)->whereBetween('date', [$DateAndTimeInicio, $DateAndTimeFin])->where('coins_id', 1)->get();

        foreach ($total_pen as $key => $p) {
            $totalVentasDiaSoles += $p->total;
        }
        $total_usd = Order::where('companies_id', $company)->where('cash_registers_id', $cja)->where('proof_payments_id', '!=', 4)->whereBetween('date', [$DateAndTimeInicio, $DateAndTimeFin])->where('coins_id', 2)->get();

        foreach ($total_usd as $key => $p) {
            $totalVentasDiaDolares += ($p->total * $p->exchange_rate);
            $totalVentasDolares += $p->total;
        }
        $total_pen_usd = ($totalVentasDiaSoles + $totalVentasDiaDolares);

        // PAGOS
        $totalPagosDiaSoles = 0;
        $totalPagosDiaDolares = 0;
        // Calcular total en soles
        $total_pago_pen = Order::join("account_receivables", "account_receivables.orders_id", "=", "orders.id")
            ->join("account_receivable_details", "account_receivable_details.account_receivables_id", "=", "account_receivables.id")
            ->where("orders.coins_id", "=", 1)->where('cash_registers_id', $cja)->where('orders.companies_id', $company)->select("*")
            ->whereBetween('account_receivable_details.date', [$DateAndTimeInicio, $DateAndTimeFin])->get();
        foreach ($total_pago_pen as $key => $p) {
            $totalPagosDiaSoles += $p->amount;
        }
        // calcular total en dolares
        $total_pago_usd = Order::join("account_receivables", "account_receivables.orders_id", "=", "orders.id")
            ->join("account_receivable_details", "account_receivable_details.account_receivables_id", "=", "account_receivables.id")
            ->where("orders.coins_id", "=", 2)->where('cash_registers_id', $cja)->where('orders.companies_id', $company)->select("*")
            ->whereBetween('account_receivable_details.date', [$DateAndTimeInicio, $DateAndTimeFin])->get();

        foreach ($total_pago_usd as $key => $p) {
            $totalPagosDiaDolares += $p->amount;
        }
        // FIN PAGOS

        // COMPRAS
        $totalComprasDiaSoles = 0;
        $totalComprasDiaDolares = 0;

        $totalC_pen = Purchase::where('companies_id', $company)->whereBetween('date', [$DateAndTimeInicio, $DateAndTimeFin])->where('coins_id', 1)->get();

        foreach ($totalC_pen as $key => $p) {
            $totalComprasDiaSoles += $p->total;
        }
        $totalC_usd = Purchase::where('companies_id', $company)->whereBetween('date', [$DateAndTimeInicio, $DateAndTimeFin])->where('coins_id', 2)->get();

        foreach ($totalC_usd as $key => $p) {
            $totalComprasDiaDolares += $p->total;
        }

        // PRODUCTOS
        $totInversion = 0;
        $number_products = Product::where('companies_id', $company)->get();

        foreach ($number_products as $key => $p) {
            $totInversion += ($p->purchase_price * $p->stock);
        }

        // Obtener ganancias del dia
        $total_ganancia = 0;
        $gananciaTotal = Order::join("order_details", "orders.id", "=", "order_details.orders_id")
            ->join("products", "products.id", "=", "order_details.products_id")
            ->where('orders.companies_id', $company)->where('orders.cash_registers_id', $cja)
            ->whereBetween('orders.date', [$DateAndTimeInicio, $DateAndTimeInicio])
            ->select("products.purchase_price", "order_details.quantity")->get();
        foreach ($gananciaTotal as $key => $p) {
            $total_ganancia += ($p->purchase_price * $p->quantity);
        }
        return Inertia::render('Reports/Index', [
            // Datos Ventas
            'soles' => number_format($totalVentasDiaSoles, 2),
            'dolares' => number_format($totalVentasDolares, 2),
            'totalVentas' => number_format($total_pen_usd, 2),
            'totalPagosSoles' => number_format($totalPagosDiaSoles, 2),
            'totalPagosDolares' => number_format($totalPagosDiaDolares, 2),
            'totalPrecioCompra' => number_format($total_pen_usd - $total_ganancia, 2),
            'totOrders' => Order::where('companies_id', $company)->where('cash_registers_id', $cja)
                ->where('proof_payments_id', '!=', 4)->whereBetween('date', [$DateAndTimeInicio, $DateAndTimeFin])->count(),
            'totPagos' => Order::join("account_receivables", "account_receivables.orders_id", "=", "orders.id")
                ->join("account_receivable_details", "account_receivable_details.account_receivables_id", "=", "account_receivables.id")
                ->where("orders.coins_id", "=", 2)->where('orders.cash_registers_id', $cja)->where('orders.companies_id', $company)->select("*")
                ->whereBetween('account_receivable_details.date', [$DateAndTimeInicio, $DateAndTimeFin])->count(),
            // N° Compras
            'comprasSoles' => number_format($totalComprasDiaSoles, 2),
            'comprasDolares' => number_format($totalComprasDiaDolares, 2),
            // N° Productos
            'totProducts' => Product::where('companies_id', $company)->count(),
            'inversionTotal' => number_format($totInversion, 2),
            'consultaCaja' => $cja,
            'dateInicio' => $DateAndTimeInicio,
            'dateFin' => $DateAndTimeFin,
            // lista Ventas
            'orders' => Order::where('companies_id', $company)->where('cash_registers_id', $cja)->where('proof_payments_id', '!=', 4)->whereBetween('date', [$DateAndTimeInicio, $DateAndTimeFin])->get()->map(function ($p) {
                return [
                    'id' => $p->id,
                    'companies_id' => $p->companies_id,
                    'company_name' => Company::find($p->companies_id)->name,
                    'customers_id' => $p->customers_id,
                    'customers_name' => Customer::find($p->customers_id)->name,
                    'payment_methods_id' => $p->payment_methods_id,
                    'payment_method' => PaymentMethod::find($p->payment_methods_id)->description,
                    'proof_payments_id' => $p->proof_payments_id,
                    'proof_payment' => ProofPayment::find($p->proof_payments_id)->name,
                    'coins_id' => $p->coins_id,
                    'coin' => Coin::find($p->coins_id)->code,
                    'documents_id' => $p->documents_id,
                    'documents_name' => Document::find($p->documents_id)->name,
                    'voucher_number' => $p->voucher_number,
                    'exchange_rate' => $p->exchange_rate,
                    'total' => $p->total,
                    'date' => $p->date,
                    'state' => $p->state,
                    'state_name' => $p->state == 1 ? 'Registrado' : 'Pendiente',
                    'description' => $p->description,
                    'details' => OrderDetail::where('orders_id', $p->id)->get()->map(function ($d) {
                        return [
                            'id' => $d->id,
                            'orders_id' => $d->orders_id,
                            'products_id' => $d->products_id,
                            'product_name' => Product::find($d->products_id)->name,
                            'mark_name' => Mark::find(Product::find($d->products_id)->marks_id)->name,
                            'product_purchase_price' => Product::find($d->products_id)->purchase_price,
                            'quantity' => $d->quantity,
                            'price' => $d->price,
                            'discount' => $d->discount,
                            'igv' => $d->igv,
                            'subTotal' => $d->subTotal,
                        ];
                    }),
                ];
            }),
                        // lista de pagos
                        'listaPagos' => Order::join("account_receivables", "account_receivables.orders_id", "=", "orders.id")
                        ->join("account_receivable_details", "account_receivable_details.account_receivables_id", "=", "account_receivables.id")
                        ->where('orders.cash_registers_id', $cja)->where('orders.companies_id', $company)
                        ->whereBetween('account_receivable_details.date', [$DateAndTimeInicio, $DateAndTimeFin])
                        ->select(
                            "orders.id AS idOrder",
                            "orders.customers_id AS idCustomer",                    
                            "orders.payment_methods_id AS idPayment",
                            "orders.proof_payments_id AS idProof",
                            "orders.coins_id AS idMoneda",
                            "orders.exchange_rate AS exchange_rate",
                            "orders.total AS total",
                            "account_receivable_details.date AS fecha",
                            "account_receivable_details.amount AS monto",                    
                        )->get()->map(function ($p) {
                            return [
                                'date' => $p->fecha,
                                'customers_name' => Customer::find($p->idCustomer)->name,
                                'payment_method' => PaymentMethod::find($p->idPayment)->description,
                                'proof_payment' => ProofPayment::find($p->idProof)->name,
                                'amount' => $p->monto,
                                'coin' => Coin::find($p->idMoneda)->code,
                                'exchange_rate' => $p->exchange_rate,
                                'total' => $p->total,
                                'date_order' => $p->date,
                                'details' => OrderDetail::where('orders_id', $p->idOrder)->get()->map(function ($d) {
                                    return [
                                        'id' => $d->id,
                                        'orders_id' => $d->orders_id,
                                        'products_id' => $d->products_id,
                                        'product_name' => Product::find($d->products_id)->name,
                                        'mark_name' => Mark::find(Product::find($d->products_id)->marks_id)->name,
                                        'product_purchase_price' => Product::find($d->products_id)->purchase_price,
                                        'quantity' => $d->quantity,
                                        'price' => $d->price,
                                        'discount' => $d->discount,
                                        'igv' => $d->igv,
                                        'subTotal' => $d->subTotal,
                                    ];
                                }),
                            ];
                        }),

            'totPurchases' => Purchase::where('companies_id', $company)->whereBetween('date', [$DateAndTimeInicio, $DateAndTimeFin])->count(),
            // Lista de Compras
            'purchases' => Purchase::where('companies_id', $company)->whereBetween('date', [$DateAndTimeInicio, $DateAndTimeFin])->get()->map(function ($p) {
                return [
                    'id' => $p->id,
                    'companies_id' => $p->companies_id,
                    'company_name' => Company::find($p->companies_id)->name,
                    'providers_id' => $p->providers_id,
                    'provider_name' => Provider::find($p->providers_id)->name,
                    'payment_methods_id' => $p->payment_methods_id,
                    'payment_method' => PaymentMethod::find($p->payment_methods_id)->description,
                    'proof_payments_id' => $p->proof_payments_id,
                    'proof_payment' => ProofPayment::find($p->proof_payments_id)->name,
                    'coins_id' => $p->coins_id,
                    'coin' => Coin::find($p->coins_id)->code,
                    'voucher_number' => $p->voucher_number,
                    'total' => $p->total,
                    'date' => $p->date,
                    'state' => $p->state,
                    'exchange_rate' => $p->exchange_rate,
                    'state_name' => $p->state == 1 ? 'Registrado' : 'Pendiente',
                    'description' => $p->description,
                    'details' => PurchaseDetail::where('purchases_id', $p->id)->get()->map(function ($d) {
                        return [
                            'id' => $d->id,
                            'companies_id' => $d->companies_id,
                            'purchases_id' => $d->purchases_id,
                            'products_id' => $d->products_id,
                            'mark_name' => Mark::find(Product::find($d->products_id)->marks_id)->name,
                            'product_name' => Product::find($d->products_id)->name,
                            'amount' => $d->amount,
                            'price' => $d->price,
                            'transporte' => $d->transporte,
                            'igv' => $d->igv,
                            'subTotal' => $d->total,
                        ];
                    }),
                ];
            }),
            // Lista Productos
            'products' => Product::where('companies_id', $company)->get()->map(function ($p) {
                return [
                    'id' => $p->id,
                    'companies_id' => $p->companies_id,
                    'warehouses_id' => $p->warehouses_id,
                    'categories_id' => $p->categories_id,
                    'marks_id' => $p->marks_id,
                    'marks_name' => Mark::find($p->marks_id)->name,
                    'measures_id' => $p->measures_id,
                    'providers_id' => $p->providers_id,
                    'type' => $p->type,
                    'name' => $p->name,
                    'code' => $p->code,
                    'bar_code' => $p->bar_code,
                    'stock' => $p->stock,
                    'purchase_price' => $p->purchase_price,
                    'sale_price' => $p->sale_price,
                    'price_by_unit' => $p->price_by_unit,
                    'wholesale_price' => $p->wholesale_price,
                    'special_price' => $p->special_price,
                    'stock_min' => $p->stock_min,
                    'description' => $p->description,
                    'state' => $p->state,
                    'expiration_date' => $p->expiration_date,
                    'totalInversion' => number_format(($p->purchase_price * $p->stock), 2),
                ];
            }),
            'colors' => Customizer::where('companies_id', $company)->get(),
            'company' => Company::find(Auth::user()->companies_id),
            'cashRegisters' => CashRegister::where('companies_id', $company)->get(),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
