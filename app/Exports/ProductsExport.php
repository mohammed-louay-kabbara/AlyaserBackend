<?php
namespace App\Exports;
use App\Models\Product;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class ProductsExport implements FromCollection, WithHeadings, WithMapping
{
    public function collection()
    {
        // جلب المنتجات مع الأصناف لتجنب مشكلة N+1 Query
        return Product::with('category')->get();
    }

    public function headings(): array
    {
        return ['اسم المنتج', 'سعر القطعة', 'سعر الجملة', 'اسم الصنف'];
    }

    public function map($product): array
    {
        return [
            $product->name,
            $product->retail_price,
            $product->wholesale_price,
            $product->category ? $product->category->name : 'غير مصنف',
        ];
    }
}