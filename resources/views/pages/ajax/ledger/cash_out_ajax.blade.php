<input type="text" hidden name="cash_out_id" value="{{ $cash_out_ajax->inv_pro_inv_id }}">
<table class="table table-bordered table-striped">
        <thead>
            <tr>
                <th>Income</th>
                <th>Supplier</th>
                <th>Description</th>
                <th>Cash Out</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>
                    <select name="expense" id="" class="js-example-basic-single anything">
                        <option value="">Select One</option>
                        @foreach ($ledger_category as $cat)
                        <optgroup label="{{ $cat->inv_ledg_cat_category_name }}">
                            @foreach ($cat->getLedgers as $ledg)
                                <option value="{{ $ledg->inv_ledg_id }}" {{ ($ledg->inv_ledg_id==$cash_out_ajax->inv_pro_inv_exp_id)? 'selected':'' }}>
                                    {{ $ledg->inv_ledg_ledger_name }}
                                </option>
                            @endforeach
                        </optgroup> 
                        @endforeach
                    </select>
                </td>
                <td>
                    <select name="supplier" id="" class="js-example-basic-single anything">
                        <option value="">Select One</option>
                        @foreach ($suppliers as $sup)
                        <option value="{{ $sup->inv_sup_id }}" {{ ($sup->inv_sup_id==$cash_out_ajax->inv_pro_inv_party_id)? 'selected' : '' }}>
                            {{ $sup->inv_sup_person }}
                        </option>
                        @endforeach
                    </select>
                </td>
                <td>
                    <input type="text" class="form-control" name="exp_desc" value="{{ $cash_out_ajax->inv_pro_inv_tran_desc }}">
                </td>
                <td class="">
                    <input type="text" class="form-control text-right" name="exp_cash_out" value="{{ $cash_out_ajax->inv_pro_inv_credit }}">
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