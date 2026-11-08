@extends('layouts.master')
@section('title', 'Quotations')

@section('customer_relation_class', 'active')
@section('quotation_class', 'active')
@section('quotations_manage', 'active')

@section('content')
<div class="row">
    <div class="col-sm-12">
        <div class="panel panel-bd lobidrag">
            <div class="panel-heading">
                <div class="panel-title">
                    <h4>Quotations List</h4>
                </div>
            </div>

            <div class="panel-body">
                <!-- Table for displaying quotations -->
                <table class="table table-bordered table-striped table-hover datatable">
                    <thead>
                        <tr>
                            <th>Customer</th>
                            <th>Entry By</th>
                            <th>Date</th>
                            <th>Expiry Date</th>
                            <th>Total Amount</th>
                            <th>Discount</th>
                            <th>Final Amount</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($quotations as $quotation)
                            <tr>
                         <td>{{ $quotation->customer->qb_company_name }} ({{ $quotation->customer->qb_name }})</td>

                                <td>
                {{ \App\Models\Admin_user::where('au_id', $quotation->qt_entry_by)->value('au_user_id') }}
            </td>
                                <td>{{ \Carbon\Carbon::parse($quotation->qt_date)->format('Y-m-d') }}</td>
                                <td>{{ \Carbon\Carbon::parse($quotation->qt_expiry_date)->format('Y-m-d') }}</td>
                                <td>{{ number_format($quotation->qt_total_amount, 2) }}</td>
                                <td>{{ number_format($quotation->qt_discount, 2) }}</td>
                                <td>{{ number_format($quotation->qt_final_amount, 2) }}</td>
                                <td>{{ $quotation->qt_status }}</td>
                                <td>
                                    <!-- Edit and Delete buttons -->
                                    <!--<a href="{{ route('quotations.edit', $quotation->qt_id) }}" class="btn btn-primary btn-sm">-->
                                    <!--    <i class="fa fa-pencil"></i> Edit-->
                                    <!--</a>-->
                                                   <a href="{{ route('quotations.print', $quotation->qt_id) }}" class="btn btn-primary btn-sm">
    <i class="fa fa-print"></i> Print
</a>

                                    <form action="{{ route('quotations.destroy', $quotation->qt_id) }}" method="POST" style="display:inline-block;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure?')">
                                            <i class="fa fa-trash"></i> Delete
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

                <!-- Pagination Links -->
                <div class="pagination">
                    {{ $quotations->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
