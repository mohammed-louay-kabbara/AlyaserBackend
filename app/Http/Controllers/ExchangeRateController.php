<?php

namespace App\Http\Controllers;


use App\Http\Controllers\Controller;
use App\Models\exchange_rate;
use Illuminate\Http\Request;

class ExchangeRateController extends Controller
{
    public function index()
    {
        $ExchangeRates=exchange_rate::get();
        return response()->json($ExchangeRates, 200);
    }
    public function get_exchange_rate(){
        $ExchangeRate=exchange_rate::where('is_default',true)->first();
        return response()->json($ExchangeRate, 200);
    }

    public function store(Request $request)
    {
        $request->validate([
            'currency_name' => 'required|',
            'rate'          => 'required|numeric',
            'is_default'    => 'boolean'
        ]);
        // إذا تم تعيين هذه العملة كافتراضية، يجب إلغاء الافتراضية عن البقية
        if ($request->is_default) {
            exchange_rate::where('is_default', true)->update(['is_default' => false]);
        }

        $rate = exchange_rate::create($request->all());
        return response()->json($rate, 201);
    }

    public function update(Request $request, $id)
    {
        $rate = exchange_rate::findOrFail($id);
        
        // إذا تم تعيين هذه العملة كافتراضية، يجب إلغاء الافتراضية عن البقية
        if ($request->is_default) {
            exchange_rate::where('id', '!=', $id)->where('is_default', true)->update(['is_default' => false]);
        }
        
        $rate->update($request->all());
        return response()->json($rate, 200);
    }

    public function destroy($id)
    {
        $rate = exchange_rate::findOrFail($id);
        $rate->delete();
        return response()->json(['message' => 'Exchange rate deleted successfully'], 200);
    }
}