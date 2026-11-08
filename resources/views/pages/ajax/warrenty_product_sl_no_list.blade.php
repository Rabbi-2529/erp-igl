
@php($pro_sl_no = explode(',',$content->inv_pro_temp_slno))
@php($loop_iteration = 0)
@foreach ($pro_sl_no as $sl_no)
    @if($sl_no != '')
    
        @php($loop_iteration++)
        <li class="list-group-item">{{ $loop_iteration }} | {{ $sl_no }} | 
            <a href="javascript:void(0);" class="btn btn-info btn-sm" onclick="remove_product_sl('{{ $content->inv_pro_temp_pro_id }}', '{{ $sl_no }}')"><span class="fa fa-remove" style="font-size: 20px; color: red;"></span></a>
        </li>
    @endif
@endforeach
