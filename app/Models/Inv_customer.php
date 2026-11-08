<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Auth;


class Inv_customer extends Model
{
    //
    protected $table = 'inv_customers';
    protected $fillable =
    [
        'inv_cus_id',
        'inv_cus_com_id',
        'inv_cus_branch_id',
        'inv_cus_name',
        'inv_cus_com_name',
        'inv_cus_mobile',
        'inv_cus_email',
        'inv_cus_address',
        'inv_cus_website',
        'inv_cus_type',
        'inv_cus_customer_type',
        'inv_cus_status',
        'inv_cus_submit_by',
        'inv_cus_submit_at',
        'inv_cus_update_by',
        'inv_cus_update_at',
    ];
    protected $primaryKey = 'inv_cus_id';
    public $incrementing = true;
    public $timestamps = false;

    public function cus_submit(){
        return $this->belongsTo('App\Models\Admin_user','inv_cus_submit_by','au_id');
    }

    public function getCustomerInventoryInfo()
    {
        return $this->belongsTo('App\Models\Inv_customer_inventrory','inv_cus_inv_cus_id','inv_cus_id');
    }
    public static function getCustomerCompany($customer_id)
    {

        return Inv_customer::where('inv_cus_id',$customer_id)->first();

    }


    public static function getNewCustomerMemoNo() {
        $com = Auth::user()->au_company_id;

        $last_pro_inv = Inv_product_inventory::where('inv_pro_inv_com_id', $com)
            ->where('inv_pro_inv_deal_type', 2)
            ->where('inv_pro_inv_tran_type', 3)
            ->orderBy('inv_pro_inv_id', 'DESC')
            ->first();
        if(!empty($last_pro_inv)) {
            $last_pro_inv_memo_no = $last_pro_inv->inv_pro_inv_invoice_no;
            $last_data = substr($last_pro_inv_memo_no, 15);
            if(is_numeric($last_data)) {
                $last_number = $last_data + 1;
                $last_number_length = strlen($last_number);
                if ($last_number_length < 6) {
                    $less_number = 6-$last_number_length;
                    $sl_prefix = "";
                    for ($x=0; $x <$less_number ; $x++) {
                        $sl_prefix = $sl_prefix . "0";
                    }
                    $last_number = $sl_prefix . $last_number;
                }

                $new_memo_no = "INVPC".$com.date('Y').($last_number);
            } else {
                $new_memo_no = "INVPC".$com.date('Y')."000001";
            }
        } else {
            $new_memo_no = "INVPC".$com.date('Y')."000001";
        }

        return $new_memo_no;
    }

      //=============18-11-19 ======================
      public static function getCustomerNameById($customer_id)
      {
         return  Inv_customer::where('inv_cus_id',$customer_id)->first()->inv_cus_name;
      }

}
