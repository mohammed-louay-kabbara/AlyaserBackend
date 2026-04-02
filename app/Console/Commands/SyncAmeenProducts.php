<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\Product;

class SyncAmeenProducts extends Command
{
    // الأمر الذي ستنفذه في التيرمينال لتبدأ المزامنة
    protected $signature = 'ameen:sync-products';

    protected $description = 'Sync products from Al-Ameen database to Laravel app';

    public function handle()
    {
        $this->info('Starting product synchronization...');

        // استخراج المواد التي ليست "مخفية" فقط (bHide = 0) من جدول mt000
        $ameenProducts = DB::connection('ameen')
                            ->table('mt000')
                            ->select('GUID', 'Name', 'Retail', 'Whole', 'Qty')
                            ->where('bHide', 0) 
                            ->get();

        $bar = $this->output->createProgressBar(count($ameenProducts));

        foreach ($ameenProducts as $product) {
            // تحديث المنتج إذا كان موجوداً مسبقاً، أو إنشاؤه من جديد
            Product::updateOrCreate(
                // الشرط الذي يبحث به (المفتاح الفريد)
                ['ameen_guid' => $product->GUID],
                
                // البيانات المراد إدخالها أو تحديثها
                [
                    'name'            => $product->Name,
                    'retail_price'    => $product->Retail ?? 0,
                    'wholesale_price' => $product->Whole ?? 0,
                    'quantity'        => $product->Qty ?? 0,
                ]
            );
            $bar->advance();
        }

        $bar->finish();
        $this->info("\nSynchronization completed successfully!");
    }
}