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
                                            <input type="checkbox" name="" id="statusToggle{{ $user->au_id }}" data-size="mini" value="" data-toggle="toggle" data-on="Active" data-off="Inactive" data-user-id="{{ $user->au_id }}" {{ $user->au_status == 1 ? 'checked' : '' }}>
                                            <label for="statusToggle{{ $user->au_id }}"></label>
                                        </div>
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
        $(document).on('change', '.toggle-switch input[type="checkbox"]', function () {
            var userId = $(this).data('user-id');
            var status = this.checked ? 1 : 0;

            var route = '';

            if (status === 1) {
                route = 'user_list_activate/' + userId;
            } else {
                route = 'user_list_deactivate/' + userId;
            }
            $.ajax({
                url: route,
                method: 'GET',
                success: function (response) {
                    console.log(response);
                },
                error: function (xhr, status, error) {
                    console.error(error);
                }
            });
        });
    });

</script>


@endsection