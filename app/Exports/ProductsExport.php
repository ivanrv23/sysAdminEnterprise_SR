<?php

namespace App\Exports;

use App\Models\Company;
use App\Models\Customizer;
use App\Models\Product;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Concerns\WithDrawings;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ProductsExport implements
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
        $company_id = Auth::user()->companies_id;
        return
            Product::select("warehouses.name AS almacen", "warehouses.name AS almacen", "products.name AS producto", "marks.name AS marca", "products.purchase_price", "products.sale_price", "products.stock")
            ->join("companies", "companies.id", "=", "products.companies_id")
            ->join("warehouses", "warehouses.id", "=", "products.warehouses_id")
            ->join("categories", "categories.id", "=", "products.categories_id")
            ->join("marks", "marks.id", "=", "products.marks_id")
            ->join("measures", "measures.id", "=", "products.measures_id")
            ->join("providers", "providers.id", "=", "products.providers_id")
            ->where("products.companies_id", "=", $company_id)->orderBy('warehouses_id')
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
            'ALMACÉN',
            'PRODUCTO',
            'MARCA',
            'PRECIO COMPRA',
            'PRECIO VENTA',
            'STOCK',
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

                $event->sheet->getDelegate()->getStyle('B6:G6')
                    ->getFill()
                    ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                    ->getStartColor()
                    ->setARGB('B0BEC5');

                $cells = "B1:G1";
                $company = Auth::user()->companies_id;
                $company_name = Company::find($company)->name;
                $event->sheet->mergeCells($cells);
                $event->sheet->getDelegate()->getStyle($cells)->getFont()->setBold(true);
                $event->sheet->getDelegate()->getStyle($cells)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
                $event->sheet->getDelegate()->getStyle($cells)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $event->sheet->setCellValue('B1', 'REPORTE PRODUCTOS');

                $event->sheet->mergeCells("E3:G3");
                $event->sheet->getDelegate()->getStyle("E3:G3")->getFont()->setBold(true);
                $event->sheet->getDelegate()->getStyle("E3:G3")->getAlignment()->setVertical(Alignment::HORIZONTAL_RIGHT);
                $event->sheet->getDelegate()->getStyle("E3:G3")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                $event->sheet->setCellValue('E3', $company_name);
            },
        ];
    }
    public function startCell(): string
    {
        return 'B6';
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
            6    => ['font' => ['bold' => true, 'size' => 12]],
        ];
    }
}
