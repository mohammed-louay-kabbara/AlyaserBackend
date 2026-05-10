<?php
namespace App\Exports;
use App\Models\Product;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class ProductsExport implements FromCollection, WithHeadings, WithMapping
{
    protected $products;

    public function __construct($products = null)
    {
        $this->products = $products;
    }

    public function collection()
    {
        // إذا تم تمرير منتجات محددة، استخدمها، وإلا جلب جميع المنتجات
        if ($this->products) {
            return $this->products;
        }
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