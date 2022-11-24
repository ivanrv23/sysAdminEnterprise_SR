<?php

namespace App\Http\Controllers;

use App\Exports\ProductsExport;
use App\Models\BankAccount;
use App\Models\Category;
use App\Models\Company;
use App\Models\Customer;
use App\Models\Customizer;
use App\Models\Mark;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\Presentation;
use App\Models\Product;
use App\Models\ProofPayment;
use App\Models\Quotation;
use App\Models\QuotationDetail;
use App\Models\Warehouse;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;

class PrintController extends Controller
{
    
    public function ventas(){
        // Obteniendo el id enviado por url
        $id = $_REQUEST['id'];
        
        $company = Company::find(Auth::user()->companies_id);
        $customizer = Customizer::where('companies_id', Auth::user()->companies_id)->get()[0];
        $total = 0;        
        $order = Order::find($id); // Buscando venta
        $customers_name=Customer::find($order->customers_id)->name;
        $order_details = OrderDetail::where('orders_id', $order->id)->get()->map(function ($o)
        {
            return [
                'quantity' => $o->quantity,
                'product' => Product::find($o->products_id)->name,
                // 'presentation' => Presentation::find($o->presentations_id)->name,
                'mark_name' => Mark::find(Product::find($o->products_id)->marks_id)->name,
                'price' => $o->price,
                'igv' => $o->igv,
                'subTotal' => $o->subTotal
            ];
        });
        
        // return  $order_details;      
        // return view('pdf.invoice', compact('order', 'order_details', 'company', 'customizer'));
        $pdf = Pdf::setPaper([0,0,226.772,1500], 'portrait')->loadView('pdf.ventas', compact('order', 'order_details', 'company', 'customizer', 'total','customers_name'));
        
        // return $pdf->download('invoice.pdf');
        return $pdf->stream('Ventas.pdf');
    }
    public function cotizacion()
    {
        // Obteniendo el id enviado por url
        $id = $_REQUEST['id'];

        $company = Company::find(Auth::user()->companies_id);
        $customizer = Customizer::where('companies_id', Auth::user()->companies_id)->get()[0];
        $igv = 0;
        $total = 0;
        $subTot=0;
        $quotation = Order::find($id); // Buscando cotización
        $customers_name=Customer::find($quotation->customers_id)->name;
        $nraccount = BankAccount::where('companies_id', Auth::user()->companies_id)->count();
        if($nraccount>0){
            $account = BankAccount::where('companies_id', Auth::user()->companies_id)->get()[0];
        }else{
            $account=null;
        }
        $quotation_details = OrderDetail::where('orders_id', $quotation->id)->get()->map(function ($o) {
            return [
                'quantity' => $o->quantity,
                'product' => Product::find($o->products_id)->name,
                'presentation' => Presentation::find($o->presentations_id)->name,
                'mark_name' => Mark::find(Product::find($o->products_id)->marks_id)->name,
                'price' => $o->price,
                'igv' => $o->igv,
                'subTotal' => $o->subTotal
            ];
        });
        $pdf = Pdf::loadView('pdf.cotizacion', compact('quotation','customers_name', 'quotation_details', 'company', 'customizer', 'total','igv','subTot','account'));

        return $pdf->stream('cotizacion.pdf');
    }
    public function productos()
    {
        $company = Company::find(Auth::user()->companies_id);
        $customizer = Customizer::where('companies_id', Auth::user()->companies_id)->get()[0];
        $products = Product::where('companies_id', $company->id)->where('type', 'Producto')->orderBy('warehouses_id')->get()->map(function ($p) {
            return [
                'id' => $p->id,
                'warehouses_id' => $p->warehouses_id,
                'warehouses_name' => Warehouse::find($p->warehouses_id)->name,
                'categories_id' => $p->categories_id,
                'categories_name' => Category::find( $p->categories_id)->name,
                'marks_id' => $p->marks_id,
                'marks_name' => Mark::find($p->marks_id)->name,
                'measures_id' => $p->measures_id,
                'providers_id' => $p->providers_id,
                'name' => $p->name,
                'type' => $p->type,
                'code' => $p->code,
                'bar_code' => $p->bar_code,
                'stock' => $p->stock,
                'purchase_price'=>$p->purchase_price,
                'sale_price' => $p->sale_price,
                'price_by_unit' => $p->price_by_unit,
                'wholesale_price' => $p->wholesale_price,
                'special_price' => $p->special_price,
                'description' => $p->description,
                'state' => $p->state,
            ];
        });
        $pdf = Pdf::loadView('pdf.productos', compact('products', 'company', 'customizer'));

        return $pdf->stream('productos.pdf');
    }
}
