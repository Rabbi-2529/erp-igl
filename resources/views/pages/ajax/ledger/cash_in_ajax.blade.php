<input type="text" name="cash_in_id" hidden value="{{ $cash_in_ajax->inv_pro_inv_id }}">
<table class="table table-bordered table-striped">
        <thead>
            <tr>
                <th>Income</th>
                <th>Customer</th>
                <th>Description</th>
                <th>Cash In</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td style="">
                    <select name="income" id="" class="js-example-basic-single anything">
                        <option value="">Select One</option>
                        @foreach ($ledger_category as $cat)
                        <optgroup label="{{ $cat->inv_ledg_cat_category_name }}">
                            @foreach ($cat->getLedgers as $ledg)
                                <option value="{{ $ledg->inv_ledg_id }}" {{ ($ledg->inv_ledg_id==$cash_in_ajax->inv_pro_inv_exp_id)? 'selected':'' }}>
                                    {{ $ledg->inv_ledg_ledger_name }}
                                </option>
                            @endforeach
                        </optgroup> 
                        @endforeach
                    </select>
                </td>
                <td>
                    <select name="customer" id="" class="js-example-basic-single anything">
                        <option value="">Select One</option>
                        @foreach ($customers as $cus)
                        <option value="{{ $cus->inv_cus_id }}" {{ ($cus->inv_cus_id==$cash_in_ajax->inv_pro_inv_party_id)? 'selected' : '' }}>
                            {{ $cus->inv_cus_name }}
                        </option>
                        @endforeach
                    </select>
                </td>
                <td>
                    <input type="text" class="form-control" name="desc" value="{{ $cash_in_ajax->inv_pro_inv_tran_desc }}">
                </td>
                <td class="">
                    <input type="text" class="form-control text-right" name="cash_in" value="{{ $cash_in_ajax->inv_pro_inv_credit }}">
                </td>
            </tr>
        </tbody>
    </table>
    <script>
        $(document).ready(function() {
    $('.js-example-basic-single').select2();
    });
    </script>
    <style>
        .select2-container--open .select2-dropdown--below{
            width: 185px !important;
        }
        .anything{
            width: 130px !important;
        }
    </style>