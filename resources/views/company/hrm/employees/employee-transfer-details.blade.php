<div class="modal fade" id="transferEmployeeModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form id="quickTransferForm">
                @csrf
                <div class="modal-header bg-primary">
                    <h4 class="modal-title">
                        <i class="fa fa-exchange"></i> Quick Transfer Employee
                    </h4>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="employee_id" id="modalEmployeeId">
                    
                    <div class="form-group">
                        <label for="modalEmployeeName">Employee:</label>
                        <input type="text" id="modalEmployeeName" class="form-control" readonly>
                    </div>
                    
                    <div class="form-group">
                        <label for="modalCurrentBranch">Current Branch:</label>
                        <input type="text" id="modalCurrentBranch" class="form-control" readonly>
                    </div>
                    
                    <div class="form-group">
                        <label for="modalNewBranchId">Transfer To Branch:</label>
                        <select name="new_branch_id" id="modalNewBranchId" class="form-control" required>
                            <option value="">Select Destination Branch</option>
                            @foreach($allBranches as $branch)
                                <option value="{{ $branch->branch_id }}">
                                    {{ $branch->branch_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="modalTransferDate">Transfer Date:</label>
                        <input type="date" name="transfer_date" id="modalTransferDate" 
                               class="form-control" 
                               value="{{ Carbon::today()->format('Y-m-d') }}"
                               min="{{ Carbon::today()->format('Y-m-d') }}"
                               required>
                    </div>
                    
                    <div class="form-group">
                        <label for="modalTransferReason">Reason:</label>
                        <textarea name="transfer_reason" id="modalTransferReason" 
                                  class="form-control" rows="3"
                                  placeholder="Enter reason for transfer..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fa fa-exchange"></i> Transfer Employee
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function openQuickTransferModal(employeeId, employeeName, currentBranch) {
    document.getElementById('modalEmployeeId').value = employeeId;
    document.getElementById('modalEmployeeName').value = employeeName;
    document.getElementById('modalCurrentBranch').value = currentBranch;
    
    $('#transferEmployeeModal').modal('show');
}

document.getElementById('quickTransferForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    
    fetch('{{ route("company.hrm.employees.transfer.quick") }}', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            toastr.success(data.message);
            $('#transferEmployeeModal').modal('hide');
            location.reload();
        } else {
            toastr.error(data.message);
        }
    })
    .catch(error => {
        toastr.error('An error occurred. Please try again.');
        console.error('Error:', error);
    });
});
</script>