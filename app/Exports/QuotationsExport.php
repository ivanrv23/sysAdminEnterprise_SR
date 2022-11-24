<?php

namespace App\Exports;

use App\Models\Company;
use App\Models\Customer;
use App\Models\Customizer;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\Quotation;
use App\Models\QuotationDetail;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Concerns\WithDrawings;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class QuotationsExport implements
    FromCollection,
    WithHeadings,
    WithEvents,
    ShouldAutoSize,
    WithCustomStartCell,
    WithDrawings,
    WithStyles
{
    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        $id = $_REQUEST['id'];
        $quotation = Order::find($id);
        return
            OrderDetail::select(
                "order_details.quantity AS cantidad",
                "products.name AS producto",
                "marks.name AS Marca",
                "order_details.price AS precio",
                "order_details.igv AS igv",
                "order_details.subTotal AS subTotal",
            )
            ->join("products", "products.id", "=", "order_details.products_id")
            ->join("marks", "marks.id", "=", "products.marks_id")
            ->where("order_details.orders_id", "=", $quotation->id)
            ->get();
    }
    /**
     * Write code on Method
     *
     * @return response()
     */
    public function headings(): array
    {
        return [
            'CANTIDAD',
            'PRODUCTO',
            'MARCA',
            'PRECIO ',
            'IGV ',
            'SUB TOTAL',
        ];
    }

    /**
     * Write code on Method
     *
     * @return response()
     */
    public function registerEvents(): array
    {
        return [
            AfterSheet::class    => function (AfterSheet $event) {

                $event->sheet->getDelegate()->getStyle('B7:G7')
                    ->getFill()
                    ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                    ->getStartColor()
                    ->setARGB('B0BEC5');

                $cells = "B1:G1";
                $company = Auth::user()->companies_id;
                $company_name = Company::find($company)->name;
                $id = $_REQUEST['id'];
                $voucher_number = Order::find($id)->voucher_number;
                $customer_id = Order::find($id)->customers_id;
                $customer_name = Customer::find($customer_id)->name;
                $date = Order::find($id)->date;
                $event->sheet->mergeCells($cells);
                $event->sheet->getDelegate()->getStyle($cells)->getFont()->setBold(true);
                $event->sheet->getDelegate()->getStyle($cells)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
                $event->sheet->getDelegate()->getStyle($cells)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $event->sheet->setCellValue('B1', 'COTIZACION N° ' . $voucher_number);

                $event->sheet->mergeCells("E3:G3");
                $event->sheet->getDelegate()->getStyle("E3:G3")->getFont()->setBold(true);
                $event->sheet->getDelegate()->getStyle("E3:G3")->getAlignment()->setVertical(Alignment::HORIZONTAL_RIGHT);
                $event->sheet->getDelegate()->getStyle("E3:G3")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                $event->sheet->setCellValue('E3', $company_name);

                $event->sheet->mergeCells("B5:C5");
                $event->sheet->getDelegate()->getStyle("B5:C5")->getAlignment()->setVertical(Alignment::HORIZONTAL_LEFT);
                $event->sheet->getDelegate()->getStyle("B5:C5")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
                $event->sheet->setCellValue('B5', 'CLIENTE: ' . $customer_name);

                $event->sheet->mergeCells("F5:G5");
                $event->sheet->getDelegate()->getStyle("F5:G5")->getAlignment()->setVertical(Alignment::HORIZONTAL_RIGHT);
                $event->sheet->getDelegate()->getStyle("F5:G5")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                $event->sheet->setCellValue('F5', 'FECHA: ' . $date);
            },
        ];
    }
    public function startCell(): string
    {
        return 'B7';
    }
    public function drawings()
    {
        $customizer = Customizer::where('companies_id', Auth::user()->companies_id)->get()[0];
        $drawing = new Drawing();
        $drawing->setName('Logo');
        $drawing->setDescription('This is my logo');
        $drawing->setPath(public_path($customizer->logo));
        $drawing->setHeight(50);
        $drawing->setCoordinates('B2');

        return $drawing;
    }
    public function styles(Worksheet $sheet)
    {
        return [
            // Style the first row as bold text.
            7    => ['font' => ['bold' => true, 'size' => 12]],
        ];
    }
}
