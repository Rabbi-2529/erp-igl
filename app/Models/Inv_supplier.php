<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Auth;

class Inv_supplier extends Model
{
    //
    protected $table = 'inv_suppliers';
    protected $fillable =
    [
        'inv_sup_id',
        'inv_sup_com_id',
             'inv_sup_branch_id',
        'inv_sup_com_name',
        'inv_sup_address',
        'inv_sup_person',
        'inv_sup_mobile',
        'inv_sup_phone',
        'inv_sup_email',
        'inv_sup_website',
        'inv_sup_complain_num',
        'inv_sup_type',
        'inv_sup_open_due_bal',
        'inv_sup_status',
        'inv_sup_submit_by',
        'inv_sup_submit_at',
        'inv_sup_update_by',
    ];
    protected $primaryKey = 'inv_sup_id';
    public $incrementing = true;
    public $timestamps = false;

    public function sup_submit_by(){
        return $this->belongsTo('App\Models\Admin_user','inv_sup_submit_by','au_id');
    }


    public static function getSupplierCompany($supplier_id)
    {
        return Inv_supplier::where('inv_sup_id',$supplier_id)->first();
    }
    public static function getSupplierNameByID($supplier_id)
    {
        return Inv_supplier::where('inv_sup_id',$supplier_id)->first();
    }


    public static function getNewSupplierMemoNo() {
        $company_id = Auth::user()->au_company_id;
        $last_pro_inv = Inv_product_inventory::where('inv_pro_inv_com_id', $company_id)
            ->where('inv_pro_inv_deal_type', 1)
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


                $new_memo_no = "INVPS".$company_id.date('Y').($last_number);
            } else {
                $new_memo_no = "INVPS".$company_id.date('Y')."000001";
            }
        } else {
            $new_memo_no = "INVPS".$company_id.date('Y')."000001";
        }

        return $new_memo_no;
    }


}
