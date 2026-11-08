@extends('layouts.master')
@section('title','Edit Bank Loan')
@section('inventory_class','active')
@section('accounts_class','active')
@section('bank_class','active')
@section('bank_loan_class','active')
@section('bank_loan_list','active')
@section('content')
<section class="content">
    <section class="content-header">
        @if(Session::has('errmsg'))
        <div class="alert alert-danger alert-dismissible" role="alert">
            {{ session('errmsg') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
        @endif
        @if($errors->any())
        <div class="alert alert-danger alert-dismissible" role="alert">
            <ul style="margin-bottom:0;">
                @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
        @endif

        <h1>Edit Bank Loan</h1>
    </section>

    <div class="row">
        <div class="col-sm-12">
            <div class="panel panel-bd lobidrag">
                <div class="panel-heading">
                    <div class="panel-title">
                        <h3 class="box-title">
                            Edit Bank Loan: {{ $loan->inv_abl_loan_name }}
                            <a href="{{ route('accounts.bank-loan.list') }}" class="btn btn-default btn-sm pull-right">
                                <i class="fa fa-arrow-left"></i> Back to List
                            </a>
                        </h3>
                    </div>
                </div>

                <div class="panel-body">
                    <form action="{{ route('accounts.bank-loan.update', $loan->inv_abl_id) }}" method="POST" id="loanForm">
                        @csrf

                        <div class="row">
                            {{-- Bank Selection --}}
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="bank_id">Bank <span class="text-danger">*</span></label>
                                    <select name="bank_id" id="bank_id" class="form-control" required>
                                        <option value="">-- Select Bank --</option>
                                        @foreach($banks as $bank)
                                        <option value="{{ $bank->inv_abi_id }}" 
                                                {{ $loan->inv_abl_bank_id == $bank->inv_abi_id ? 'selected' : '' }}>
                                            {{ $bank->bank_info->bank_name ?? 'Unknown Bank' }} 
                                            - {{ $bank->inv_abi_account_name }}
                                        </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            {{-- Loan Name --}}
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="loan_name">Loan Name <span class="text-danger">*</span></label>
                                    <input type="text" name="loan_name" id="loan_name" class="form-control" 
                                           value="{{ old('loan_name', $loan->inv_abl_loan_name) }}" required>
                                </div>
                            </div>

                            {{-- Loan Type (Frequency) --}}
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="loan_type">Loan Type <span class="text-danger">*</span></label>
                                    <select name="loan_type" id="loan_type" class="form-control" required>
                                        <option value="1" {{ $loan->inv_abl_loan_type == 1 ? 'selected' : '' }}>Monthly</option>
                                        <option value="2" {{ $loan->inv_abl_loan_type == 2 ? 'selected' : '' }}>Quarterly</option>
                                        <option value="3" {{ $loan->inv_abl_loan_type == 3 ? 'selected' : '' }}>Half-Yearly</option>
                                        <option value="4" {{ $loan->inv_abl_loan_type == 4 ? 'selected' : '' }}>Yearly</option>
                                    </select>
                                </div>
                            </div>

                            {{-- Loan Account No --}}
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="loan_account_no">Loan Account No</label>
                                    <input type="text" name="loan_account_no" id="loan_account_no" 
                                           class="form-control" 
                                           value="{{ old('loan_account_no', $loan->inv_abl_loan_account_no) }}">
                                </div>
                            </div>

                            {{-- Interest Type --}}
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="interest_type">Interest Type</label>
                                    <select name="interest_type" id="interest_type" class="form-control">
                                        <option value="1" {{ $loan->inv_abl_interest_type == 1 ? 'selected' : '' }}>Simple</option>
                                        <option value="2" {{ $loan->inv_abl_interest_type == 2 ? 'selected' : '' }}>Compound</option>
                                    </select>
                                </div>
                            </div>

                            {{-- Principal Amount --}}
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="principal_amount">Principal Amount <span class="text-danger">*</span></label>
                                    <input type="number" step="0.01" name="principal_amount" id="principal_amount" 
                                           class="form-control" 
                                           value="{{ old('principal_amount', $loan->inv_abl_principal_amount) }}" required>
                                </div>
                            </div>

                            {{-- Interest Rate --}}
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="interest_rate">Annual Interest Rate (%) <span class="text-danger">*</span></label>
                                    <input type="number" step="0.01" name="interest_rate" id="interest_rate" 
                                           class="form-control" 
                                           value="{{ old('interest_rate', $loan->inv_abl_interest_rate) }}" required>
                                </div>
                            </div>

                            {{-- Tenure --}}
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="tenure_months">Tenure (Months) <span class="text-danger">*</span></label>
                                    <input type="number" name="tenure_months" id="tenure_months" 
                                           class="form-control" 
                                           value="{{ old('tenure_months', $loan->inv_abl_tenure_months) }}" required>
                                </div>
                            </div>

                            {{-- Disbursement Date --}}
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="disbursement_date">Disbursement Date <span class="text-danger">*</span></label>
                                    <input type="text" name="disbursement_date" id="disbursement_date" 
                                           class="form-control datepicker" 
                                           value="{{ old('disbursement_date', $loan->inv_abl_disbursement_date ? $loan->inv_abl_disbursement_date->format('d/m/Y') : date('d/m/Y')) }}" 
                                           required autocomplete="off">
                                </div>
                            </div>

                            {{-- Remarks --}}
                            <div class="col-md-8">
                                <div class="form-group">
                                    <label for="remarks">Remarks</label>
                                    <input type="text" name="remarks" id="remarks" class="form-control" 
                                           value="{{ old('remarks', $loan->inv_abl_remarks) }}">
                                </div>
                            </div>
                        </div>

                        {{-- EMI & Date Preview --}}
                        <div class="alert alert-info" id="emiPreview" style="display:none;">
                            <h5><i class="fa fa-calculator"></i> Loan Calculation Preview</h5>
                            <div class="row">
                                <div class="col-md-3">
                                    <strong>EMI Amount:</strong><br>
                                    ৳ <span id="preview_emi">0.00</span>
                                </div>
                                <div class="col-md-3">
                                    <strong>Total Installments:</strong><br>
                                    <span id="preview_installments">0</span>
                                </div>
                                <div class="col-md-3">
                                    <strong>Total Payable:</strong><br>
                                    ৳ <span id="preview_total">0.00</span>
                                </div>
                                <div class="col-md-3">
                                    <strong>Total Interest:</strong><br>
                                    ৳ <span id="preview_interest">0.00</span>
                                </div>
                            </div>
                            <hr style="margin: 10px 0;">
                            <div class="row">
                                <div class="col-md-4">
                                    <strong>Disbursement Date:</strong><br>
                                    <span id="preview_disbursement_date">—</span>
                                </div>
                                <div class="col-md-4">
                                    <strong>First EMI Date:</strong><br>
                                    <span id="preview_first_emi_date">—</span>
                                </div>
                                <div class="col-md-4">
                                    <strong>Maturity Date:</strong><br>
                                    <span id="preview_maturity_date">—</span>
                                </div>
                            </div>
                        </div>

                        <div class="form-group text-right">
                            <button type="submit" class="btn btn-success">
                                <i class="fa fa-save"></i> Update Loan
                            </button>
                            <a href="{{ route('accounts.bank-loan.list') }}" class="btn btn-default">
                                <i class="fa fa-times"></i> Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection

@section('custom_style')
<style>
    .action_btn { border: 1px solid; padding: 5px; }
    .btn_show { background-color: green; color: white; }
    .btn_delete { background-color: red; color: white; }
    .btn_edit { background-color: cornflowerblue; color: white; }
</style>
@endsection

@section('custom_script')
<script>
    $(document).ready(function() {
        // Force text type to avoid browser native date input
        $('#disbursement_date').attr('type', 'text');

        if ($.fn.datepicker) {
            $('.datepicker').datepicker({
                format: 'dd/mm/yyyy',
                autoclose: true,
                todayHighlight: true
            });
        }

        var monthsPerPeriodMap = { 1: 1, 2: 3, 3: 6, 4: 12 };

        function parseDate(str) {
            if (!str) return null;
            if (/^\d{2}\/\d{2}\/\d{4}$/.test(str)) {
                var p = str.split('/');
                return new Date(p[2], p[1] - 1, p[0]);
            }
            if (/^\d{4}-\d{2}-\d{2}$/.test(str)) {
                var p = str.split('-');
                return new Date(p[0], p[1] - 1, p[2]);
            }
            return null;
        }

        function formatDate(date) {
            if (!date) return '—';
            var dd = String(date.getDate()).padStart(2, '0');
            var mm = String(date.getMonth() + 1).padStart(2, '0');
            var yyyy = date.getFullYear();
            return dd + '/' + mm + '/' + yyyy;
        }

        function addMonths(date, months) {
            var d = new Date(date.getTime());
            d.setMonth(d.getMonth() + months);
            return d;
        }

        function updateDatePreview() {
            var disbStr  = $('#disbursement_date').val();
            var tenure   = parseInt($('#tenure_months').val()) || 0;
            var loanType = parseInt($('#loan_type').val()) || 1;
            var mpp      = monthsPerPeriodMap[loanType] || 1;

            var disbDate = parseDate(disbStr);
            if (!disbDate) {
                $('#preview_disbursement_date').text('—');
                $('#preview_first_emi_date').text('—');
                $('#preview_maturity_date').text('—');
                return;
            }

            $('#preview_disbursement_date').text(formatDate(disbDate));

            if (tenure > 0) {
                $('#preview_first_emi_date').text(formatDate(addMonths(disbDate, mpp)));
                $('#preview_maturity_date').text(formatDate(addMonths(disbDate, tenure)));
            } else {
                $('#preview_first_emi_date').text('—');
                $('#preview_maturity_date').text('—');
            }
        }

        function calculateEMI() {
            var principal = parseFloat($('#principal_amount').val()) || 0;
            var rate      = parseFloat($('#interest_rate').val()) || 0;
            var tenure    = parseInt($('#tenure_months').val()) || 0;
            var loanType  = parseInt($('#loan_type').val()) || 1;

            if (principal > 0 && rate >= 0 && tenure > 0) {
                $.ajax({
                    url: "{{ route('accounts.bank-loan.ajax-calculate-emi') }}",
                    type: 'GET',
                    data: {
                        principal: principal,
                        interest_rate: rate,
                        tenure_months: tenure,
                        loan_type: loanType
                    },
                    success: function(response) {
                        $('#preview_emi').text(response.emi);
                        $('#preview_installments').text(response.total_installments);
                        $('#preview_total').text(response.total);
                        $('#preview_interest').text(response.total_interest);
                        $('#emiPreview').show();
                    }
                });
            } else {
                $('#emiPreview').hide();
            }

            updateDatePreview();
        }

        $('#principal_amount, #interest_rate, #tenure_months, #loan_type, #disbursement_date')
            .on('keyup change blur', function() {
                clearTimeout(window.emiTimer);
                window.emiTimer = setTimeout(calculateEMI, 400);
            });

        $('#disbursement_date').on('changeDate', function() {
            calculateEMI();
        });

        // Normalize date to d/m/Y before submit
        $('#loanForm').on('submit', function(e) {
            var $dateInput = $('#disbursement_date');
            var raw = $dateInput.val();
            if (!raw) return;

            if (/^\d{2}\/\d{2}\/\d{4}$/.test(raw)) return;

            if (/^\d{4}-\d{2}-\d{2}$/.test(raw)) {
                var p = raw.split('-');
                $dateInput.val(p[2] + '/' + p[1] + '/' + p[0]);
                return;
            }

            var d = new Date(raw);
            if (!isNaN(d.getTime())) {
                var dd = String(d.getDate()).padStart(2, '0');
                var mm = String(d.getMonth() + 1).padStart(2, '0');
                var yyyy = d.getFullYear();
                $dateInput.val(dd + '/' + mm + '/' + yyyy);
            }
        });

        // Run once on load to show preview for existing loan
        calculateEMI();
    });
</script>
@endsection