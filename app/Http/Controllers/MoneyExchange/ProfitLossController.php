<?php

namespace App\Http\Controllers\MoneyExchange;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class ProfitLossController extends Controller
{
    public function profit(){
        return view('moneyExchange.profit_loss.profit');
    }
    public function loss(){
        return view('moneyExchange.profit_loss.loss');
    }
}
