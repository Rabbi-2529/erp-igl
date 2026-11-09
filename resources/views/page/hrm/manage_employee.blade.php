@extends('layouts.master')
@section('content')
@section('title', 'Manage Employee')

@section('human_resource_class','active')
@section('hrm_class','active')
@section('manage_employee_class','active')


<div class="row">
    <div class="col-sm-12">
        <div class="panel panel-bd lobidrag">
            
            <div class="panel-body">
                <style>
                    .erp-emp-filter { display: flex; flex-wrap: wrap; gap: 6px; margin-bottom: 12px; }
                    .erp-emp-filter .active { color: var(--erp-on-primary); background: var(--erp-green-btn); border-color: var(--erp-green-btn); }
                    .erp-emp-left { display: inline-block; margin-top: 4px; padding: 1px 7px; border-radius: 9px; font-size: 11px; font-weight: 700; white-space: nowrap; color: #fff; background: #5f6b66; }
                </style>
                <div class="erp-emp-filter" role="group" aria-label="Show employees">
                    <a href="{{ url('/user_list') }}" class="btn btn-default btn-sm {{ $show === 'all' ? 'active' : '' }}">All ({{ $counts['all'] }})</a>
                    <a href="{{ url('/user_list?show=working') }}" class="btn btn-default btn-sm {{ $show === 'working' ? 'active' : '' }}"><i class="fa fa-check"></i> Working ({{ $counts['working'] }})</a>
                    @if($canExit)
                        <a href="{{ url('/user_list?show=left') }}" class="btn btn-default btn-sm {{ $show === 'left' ? 'active' : '' }}"><i class="fa fa-sign-out"></i> Left ({{ $counts['left'] }})</a>
                    @endif
                </div>
                <div class="table-responsive">
                    <table id="" class="table table-bordered table-striped table-hover datatable">
                        <thead>
                            <tr>
                                <th style="width: 10px;" class="text-center">SL.</th>
                                <th style="width: 200px;">Name</th>
                                <th style="width: 60px;" class="text-center">ID</th>
                                <th style="width: 75px;" class="text-center">Mobile</th>
                                <th style="width: 120px;" class="text-center">Email</th>
                                <th style="width: 60px;" class="text-center">Image</th>
                                <th class="text-center">Team</th>
                                <th style="width: 60px;" class="text-center">Cust.</th>
                                <th style="width: 40px;" class="text-center">Flexi</th>
                                <th style="width: 40px;" class="text-center">Status</th>
                                <th style="width: 85px;" class="text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            if ($users) {
                            ?>
                               
                            <?php
                            $sl = 1;
                                foreach($users as $user){
                                    $customImagePath = asset('public/profile/' . $user->au_company_img);
                                    $imagePath = asset('assets/img/icons/user.png');
                            ?>
                            <tr>
                                <td class="text-center"><?php echo $sl;?></td>
                                <td>{{ $user->au_name }}</td>
                                <td class="text-center">{{ $user->au_user_id }}</td>
                                <td class="text-center">{{ $user->au_mobile }}</td>
                                <td class="text-center">{{ $user->au_email }}</td>

                                <td class="text-center">
                                    <img src="{{ asset(file_exists(public_path('profile/' . $user->au_company_img)) ? $customImagePath : $imagePath) }}" 
                                         width="30px" 
                                         style="border-radius: 50%;">
                                </td>
                                

                                <td class="text-center">{{ !empty($user->team_info['tl_team_name']) ? $user->team_info['tl_team_name'] : '' }}</td>
                                
                                <td class="text-center"><a href="{{ url('user/customer_list',['id' => $user->au_id]) }}" class="btn btn-success btn-sm">{{ $user->customers_count }}</a></td>

                                {{-- Load Section --}}
                                <td class="text-center">
                                    <div class="flexi-balance-container">
                                        @if($user->au_flexi_status == 0)
                                            <span class="label label-sm label-danger flexi-balance editable-flexi-balance" data-user-id="{{ $user->au_id }}" data-balance="{{ $user->au_flexi_balance }}" style="cursor: pointer;">{{ $user->au_flexi_balance }} TK</span>
                                        @elseif($user->au_flexi_status == 1)
                                            <span class="label label-sm label-primary flexi-balance editable-flexi-balance" data-user-id="{{ $user->au_id }}" data-balance="{{ $user->au_flexi_balance }}" style="cursor: pointer;">{{ $user->au_flexi_balance }} TK</span>
                                        @endif
                                    </div>
                                </td>

                                {{-- Modal For Flexi --}}
                                <div class="modal fade" id="flexiBalanceModal" tabindex="-1" role="dialog" aria-labelledby="flexiBalanceModal">
                                    <div class="modal-dialog" role="document">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                                                <h4 class="modal-title" id="flexiBalanceModal">Flexi Information</h4>
                                            </div>
                                            <div class="modal-body">
                                                <form action="{{ route('user-flexi-permission', $user->au_id) }}" method="POST">
                                                    @csrf
                                                    <div class="row">
                                                        <div class="col-md-3">
                                                            <div class="form-group">
                                                                <label for="flexiPermission">Flexi Permission:</label>
                                                                @if($user->au_flexi_status == 0)
                                                                    <a href="{{ route('user_flexi_activate', ['id' => $user->au_id]) }}" class="user-flexi-activate" data-rel="tooltip" title="Confirm">
                                                                        <span class="label label-sm label-danger">Inactive</span>
                                                                    </a>
                                                                @elseif($user->au_flexi_status == 1)
                                                                    <a href="{{ route('user_flexi_deactivate', ['id' => $user->au_id]) }}" class="user-flexi-inactivate" data-rel="tooltip" title="Confirm">
                                                                        <span class="label label-sm label-success">Active</span>
                                                                    </a>
                                                                @endif
                                                            </div>
                                                        </div>
                                                        <div class="col-md-3">
                                                            {{-- Empty column --}}
                                                        </div>
                                                        <div class="col-md-3">
                                                            <div class="form-group">
                                                                <label for="loadAmount">Load Amount:</label>
                                                                <input type="number" placeholder="Flexi Balance" class="form-control" id="totalMeal" value="{{ $user->au_flexi_balance }}" name="balance" required>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="submit" id="updateBalanceButton" data-user-id="{{ $user->au_flexi_balance }}" class="btn btn-primary pull-right">Update</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            
                                <td>
                                    <center>
                                        <div class="toggle-switch">
                                            <input type="checkbox" name="" id="statusToggle{{ $user->au_id }}" data-size="mini" value="" data-toggle="toggle" data-on="Active" data-off="Inactive" data-user-id="{{ $user->au_id }}" {{ $user->au_status == 1 ? 'checked' : '' }} @if($exits->has($user->au_id)) data-left="{{ $user->au_name }} left on {{ \Carbon\Carbon::parse($exits[$user->au_id]->hee_last_day)->format('d M Y') }}" @endif>
                                            <label for="statusToggle{{ $user->au_id }}"></label>
                                        </div>
                                        @if($exits->has($user->au_id))
                                            <span class="erp-emp-left" title="{{ \App\Services\EmployeeTenure::TYPES[$exits[$user->au_id]->hee_type] ?? '' }}">Left {{ \Carbon\Carbon::parse($exits[$user->au_id]->hee_last_day)->format('d M Y') }}</span>
                                        @endif
                                    </center>
                                </td>
                                
                                
                                
                                
                                {{-- Action section --}}
                                <td class="text-center">
                                    <div class="dropdown">
                                        <button class="btn btn-default btn-sm dropdown-toggle" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                            <i class="fa fa-gear"></i> Action
                                            <span class="caret"></span>
                                        </button>
                                        <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                                            <li>
                                                <a href="{{ url('/user_edit',['id' => $user->au_id]) }}" data-toggle="tooltip" data-placement="left" title="Update">
                                                    <i class="fa fa-pencil" aria-hidden="true"></i> Update
                                                </a>
                                            </li>
                                            <li>
                                                <a href="{{ url('/user_profile',['id' => $user->au_id]) }}" data-toggle="tooltip" data-placement="left" title="Profile">
                                                    <i class="fa fa-user" aria-hidden="true"></i> Profile
                                                </a>
                                            </li>
                                            <li>
                                                <a href="{{ route('permission',['id' => $user->au_id]) }}" data-toggle="tooltip" data-placement="left" title="Permission">
                                                    <i class="fa fa-server" aria-hidden="true"></i> Permission
                                                </a>
                                            </li>
                                            @if($canExit && !in_array((int) $user->au_user_type, [1, 3, 4], true) && (int) $user->au_id !== (int) Auth::id())
                                                @if($exits->has($user->au_id))
                                                    <li>
                                                        <a href="{{ url('/user_profile',['id' => $user->au_id]) }}#erpExitInfo" title="Left: see the profile to undo">
                                                            <i class="fa fa-sign-out" aria-hidden="true"></i> Left (see profile)
                                                        </a>
                                                    </li>
                                                @else
                                                    <li>
                                                        <a href="#" data-exit-url="{{ route('hrm.employees.exit', $user->au_id) }}" data-name="{{ $user->au_name }}" data-code="{{ $user->au_user_id }}" data-mobile="{{ $user->au_mobile }}">
                                                            <i class="fa fa-sign-out" aria-hidden="true"></i> Exit employee
                                                        </a>
                                                    </li>
                                                @endif
                                            @endif
                                        </ul>
                                    </div>
                                </td>
                            </tr>

                            <?php
                            $sl++;
                        }}
                        ?>
                        </tbody>
                        <tfoot></tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>


<script>
    $(document).ready(function () {
        $('.editable-flexi-balance').on('click', function () {
            var userId = $(this).data('user-id');
            var balance = $(this).data('balance');

            $('#newBalance').val(balance);
            $('#updateBalanceButton').data('user-id', userId);

            $('#flexiBalanceModal').modal('show');
        });

        $('#updateBalanceButton').on('click', function () {
            var userId = $(this).data('user-id');
            var newBalance = $('#newBalance').val();

            $.ajax({
                url: '/update_flexi_balance/' + userId,
                method: 'POST',
                data: { balance: newBalance },
                success: function (response) {
                    console.log(response);
                    $('#flexiBalanceModal').modal('hide');
                },
                error: function (xhr, status, error) {
                    console.error(error);
                }
            });
        });

        // User Active/Inactive Script
        // put the switch back without sending anything (when the server or the user says no)
        function putBack(box, on) {
            var t = $(box).data('bs.toggle');
            if (t) { t[on ? 'on' : 'off'](true); } else { box.checked = on; }
        }
        $(document).on('change', '.toggle-switch input[type="checkbox"]', function () {
            var box = this;
            var userId = $(this).data('user-id');
            var status = this.checked ? 1 : 0;

            var route = '';

            if (status === 1) {
                // someone marked as left: turning them on undoes the exit
                if ($(box).attr('data-left') && !confirm($(box).attr('data-left') + '. Turn them on again? This undoes the exit.')) {
                    putBack(box, false);
                    return;
                }
                route = 'user_list_activate/' + userId;
            } else {
                route = 'user_list_deactivate/' + userId;
            }
            $.ajax({
                url: route,
                method: 'GET',
                headers: { 'Accept': 'application/json' },
                success: function (response) {
                    if (response && response.ok === false) {
                        putBack(box, !status);
                        alert(response.message);
                        return;
                    }
                    if (status === 1 && $(box).attr('data-left')) { window.location.reload(); }
                },
                error: function (xhr, status, error) {
                    console.error(error);
                }
            });
        });
    });

</script>

@include('page.hrm.employees._exit_modal')

@endsection