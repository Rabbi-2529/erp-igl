
<div class="row" align="center">
  <div class="col-md-8 col-md-offset-2">
        <table id="all_list" class="table table-bordered table-striped">
          <thead>
            <tr>
              <th>SL</th>
              <th class="text-center">Reason Name</th>
              <th class="text-center">Action</th>
            </tr>
            <tbody>
              @php( $sl = 0 )
              @foreach( $reason_list as $list )
              <tr>
                <td>{{ ++$sl }}</td>
                <td align="center">{{ $list->sr_reason }}</td>
                <td align="center">
                  <a href="javascript:void(0);" onclick="open_reason_edit_modal('{{ $list->sr_slid }}', '{{ $list->sr_reason }}')" style="color:green;">
                  <span class="glyphicon glyphicon-edit"></span>
                  </a> |
                  <a href="{{ route('reason_delete',['id' => $list->sr_slid]) }}" onclick="return deleteTemp()" style="color: red;">
                    <span class="glyphicon glyphicon-remove"></span>
                  </a>
                </td>
              </tr>
              @endforeach
            </tbody>
          </thead>
        </table>
  </div>
  <script type="text/javascript">
  function deleteTemp(){
    let clickDel = confirm("Are you sure want to delete this?");
    if (clickDel == true) {
      return true;
    }else{
      return false;
    }
  }
  </script>
</div>
