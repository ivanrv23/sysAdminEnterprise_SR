<?php

namespace App\Http\Controllers;

use App\Models\AccountReceivable;
use App\Http\Requests\StoreAccountReceivableRequest;
use App\Http\Requests\UpdateAccountReceivableRequest;
use App\Models\AccountReceivableDetail;
use App\Models\CashRegister;
use App\Models\Coin;
use App\Models\Company;
use App\Models\Customer;
use App\Models\Customizer;
use App\Models\Mark;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\PettyCash;
use App\Models\Product;
use App\Models\ProofPayment;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;

class AccountReceivableController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $company = Auth::user()->companies_id;
        return Inertia::render('AccountReceivables/Index', [
            'accountReceivables' => AccountReceivable::where('companies_id', $company)->where('state', 0)->get()->map(function ($p) {
                return [
                    'id' => $p->id,
                    'orders_id' => $p->orders_id,
                    $id_cliente = Order::where('id', $p->orders_id)->value('customers_id'),
                    'customers_name' => Customer::find($id_cliente)->name,
                    $id_comprobante = Order::where('id', $p->orders_id)->value('proof_payments_id'),
                    'proof_payments_name' => ProofPayment::find($id_comprobante)->name,
                    $id_moneda = Order::where('id', $p->orders_id)->value('coins_id'),
                    'coin' => Coin::find($id_moneda)->code,
                    'exchange_rate' => Order::where('id', $p->orders_id)->value('exchange_rate'),
                    'date' => Order::where('id', $p->orders_id)->value('date'),
                    'payment' => $p->payment,
                    'debt'  => $p->debt,
                    'description' => $p->description,
                    'details' => OrderDetail::where('orders_id', $p->orders_id)->get()->map(function ($d) {
                        return [
                            'id' => $d->id,
                            'orders_id' => $d->orders_id,
                            'products_id' => $d->products_id,
                            'product_name' => Product::find($d->products_id)->name,
                            // 'marks_name' => Mark::find(Product::find($d->products_id)->id)->name,
                            'quantity' => $d->quantity,
                            'price' => $d->price,
                            'discount' => $d->discount,
                            'igv' => $d->igv,
                            'subTotal' => $d->subTotal,
                        ];
                    }),
                ];
            }),
            'company' => Company::find($company),
            'colors' => Customizer::where('companies_id', $company)->get(),

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
     * @param  \App\Http\Requests\StoreAccountReceivableRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreAccountReceivableRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\AccountReceivable  $accountReceivable
     * @return \Illuminate\Http\Response
     */
    public function show(AccountReceivable $accountReceivable)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\AccountReceivable  $accountReceivable
     * @return \Illuminate\Http\Response
     */
    public function edit(AccountReceivable $accountReceivable)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateAccountReceivableRequest  $request
     * @param  \App\Models\AccountReceivable  $accountReceivable
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateAccountReceivableRequest $request, $id)
    {
        $company = Auth::user()->companies_id;
        $accountReceivable = AccountReceivable::find($id);
        $id_order = AccountReceivable::where('id', $id)->value('orders_id');
        $orders = Order::find($id_order);
        $totalPagado=$request->payment + $request->totalPago;
        $nvPago = $request->totalPago;
        $nvDeuda = $request->debt - $request->totalPago;
        if ($nvDeuda == 0) {
            $nvestado = 1;
        } else {
            $nvestado = 0;
        };
        $accountReceivable->update([
            'payment' => $totalPagado,
            'debt' => $nvDeuda,
            'description' => $request->description,
            'state' => $nvestado,
        ]);
        $orders->update([
            'state' => $nvestado,
        ]);

        // Agregar detalle de pago con fecha
        $detallePago = new AccountReceivableDetail();
        $detallePago->companies_id = $company;
        $detallePago->account_receivables_id = $accountReceivable->id;
        $detallePago->amount = $nvPago;
        $detallePago->date = date('Y-m-d');
        $detallePago->description =  $request->description;
        $detallePago->save();

        // Caja General
        $pettyCash = PettyCash::where('companies_id', $company)->where('state', 1)->get();
        if ($pettyCash == '[]') {
            echo 'error';
        } else {
            if ($orders->coins_id == 1) {
                $pettyCash[0]->update([
                    $pettyCash[0]->amount_pen += $request->totalPago,
                ]);
            } else {
                if ($orders->coins_id == 2) {
                    $pettyCash[0]->update([
                        $pettyCash[0]->amount_usd += $request->totalPago,
                    ]);
                }
            }
        }

        // Agregar pagos segun caja seleccionada
        $cashRegister = CashRegister::where('companies_id', $company)->where('id', $orders->cash_registers_id)->get();
        if ($orders->coins_id == 1) {
            $cashRegister[0]->update([
                $cashRegister[0]->amount_pen += $request->totalPago,
            ]);
        } else {
            if ($orders->coins_id == 2) {
                $cashRegister[0]->update([
                    $cashRegister[0]->amount_usd += $request->totalPago,
                ]);
            }
        }
        return Redirect::route('accountReceivables.index')->with('message', 'Pago realizado Correctamente');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\AccountReceivable  $accountReceivable
     * @return \Illuminate\Http\Response
     */
    public function destroy(AccountReceivable $accountReceivable)
    {
        //
    }
}
