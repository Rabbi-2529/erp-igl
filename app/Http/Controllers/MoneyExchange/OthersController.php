<?php

namespace App\Http\Controllers\MoneyExchange;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\MexCountry;
use App\Models\MexCurrency;

class OthersController extends Controller
{
    public function country(){
        $countries = MexCountry::all();
        return view('moneyExchange.country.list',compact('countries'));
    }

    public function currency(){
        $currencies = MexCurrency::all();
        // dd($currencies);
        return view('moneyExchange.currency.list',compact('currencies'));
    }
}
