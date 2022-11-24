<?php

namespace App\Http\Controllers;

use App\Exports\QuotationsExport;
use App\Models\Quotation;
use App\Http\Requests\StoreQuotationRequest;
use App\Http\Requests\UpdateQuotationRequest;
use App\Models\Coin;
use App\Models\Company;
use App\Models\Customer;
use App\Models\Customizer;
use App\Models\Document;
use App\Models\Mark;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\PaymentMethod;
use App\Models\Presentation;
use App\Models\Product;
use App\Models\ProofPayment;
use App\Models\QuotationDetail;
use App\Models\Warehouse;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;
use Maatwebsite\Excel\Facades\Excel;

class QuotationController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $company = Auth::user()->companies_id;

        return Inertia::render('Quotations/Index', [
            'quotations' => Order::where('companies_id', $company)->where('proof_payments_id' , 4 )->get()->map(function ($p) {
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
        $company = Auth::user()->companies_id;
        $query = new SunatController;
        try {
            $exchange_rate = $query->exchange_rate()['compra'];
        } catch (Exception $e) {
            $exchange_rate = 0;
        }

        return Inertia::render('Quotations/Create', [
            'company' => Company::find($company),
            'customers' => Customer::where('companies_id', $company)->get(),
            'colors' => Customizer::where('companies_id', $company)->get(),
            'companies' => Company::all(),
            'presentations' => Presentation::where('companies_id', $company)->get(),
            'coins' => Coin::all(),
            'exchange_rate' => $exchange_rate,
            'nro_cotizacion' => sprintf("%08d", Quotation::where('companies_id', $company)->count() + 1),
            'products' => Product::where('companies_id', $company)->get()->map(function ($p) {
                return [
                    'id' => $p->id,
                    'categories_id' => $p->categories_id,
                    'marks_id' => $p->marks_id,
                    'marks_name' => Mark::find($p->marks_id)->name,
                    'marks' => Mark::where('id', $p->marks_id)->get(),
                    'measures_id' => $p->measures_id,
                    'providers_id' => $p->providers_id,
                    'warehouses_name' => Warehouse::find($p->warehouses_id)->name,
                    'name' => $p->name,
                    'type' => $p->type,
                    'code' => $p->code,
                    'bar_code' => $p->bar_code,
                    'stock' => $p->stock,
                    'purchase_price' => $p->purchase_price,
                    'sale_price' => $p->sale_price,
                    'price_by_unit' => $p->price_by_unit,
                    'wholesale_price' => $p->wholesale_price,
                    'special_price' => $p->special_price,
                    'description' => $p->description,
                    'state' => $p->state,
                ];
            }),

        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\StoreQuotationRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreQuotationRequest $request)
    {
        $quotation = new Quotation();

        $quotation->companies_id = $request->companies_id;
        $quotation->customers_id = $request->customers_id;
        $quotation->coins_id = $request->coins_id;
        $quotation->voucher_number = $request->nroCotizacion;
        $quotation->exchange_rate = $request->exchange_rate;
        $quotation->igv = $request->tipo_igv;
        $quotation->transporte = $request->transporte;
        $quotation->descuento = $request->descuento;
        $quotation->total = $request->total;
        $quotation->date = $request->date;
        $quotation->description = $request->description;
        $quotation->save();

        $products = $request->products;
        foreach ($products as $key => $value) {
            $quotation_details = new QuotationDetail();
            $quotation_details->companies_id  = $request->companies_id;
            $quotation_details->quotations_id = $quotation->id;
            $quotation_details->products_id = $value['productId'];
            $quotation_details->presentations_id = $value['presentationId'];
            $quotation_details->quantity = $value['quantity'];
            $quotation_details->price = $value['sale_price'];
            $quotation_details->discount = 0;
            $quotation_details->igv = $value['igv'];
            $quotation_details->subTotal = $value['subTotal'];

            $quotation_details->save();
        }


        return Redirect::route('quotations.index')->with('message', 'Cotización agregada');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Quotation  $quotation
     * @return \Illuminate\Http\Response
     */
    public function show(Quotation $quotation)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Quotation  $quotation
     * @return \Illuminate\Http\Response
     */
    public function edit(Quotation $quotation)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateQuotationRequest  $request
     * @param  \App\Models\Quotation  $quotation
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateQuotationRequest $request, Quotation $quotation)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Quotation  $quotation
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $quotation = Order::find($id);
        $quotation->delete();
        return Redirect::route('quotations.index')->with('message', 'Cotización eliminada');
    }

    public function exportCotizacion()
    {
        $id = $_REQUEST['id'];
        $voucher_number = Order::find($id)->voucher_number;
        return Excel::download(new QuotationsExport, 'Cotizacion Nro. ' . $voucher_number . '.xlsx');
    }
}
