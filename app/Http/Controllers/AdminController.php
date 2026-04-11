<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\category;
use App\Models\Product;
use App\Models\Order;
use App\Models\Offer;

class AdminController extends Controller
{
    public function index()
    {
        $users_count=User::count();
        $activated=User::where('activated',0)->count();
        $category=category::count();
        $Product_count=Product::count();
        $Product_quantity=Product::where('quantity',0)->count();
        $Order_pending=Order::where('status','pending')->count();
        $Order_processing=Order::where('status','processing')->count();
        $Offers_count=Offer::where('expires_at','<',now())->count();
         $Offers = Offer::where('expires_at', '>', now())->get();
        return view('dashboard',compact('users_count','activated','category','Product_count','Product_quantity','Order_pending','Offers_count','Order_processing','Offers'));
    }
}
