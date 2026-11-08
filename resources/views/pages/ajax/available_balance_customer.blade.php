@if($customerBalance>=500)
<span style="color: green; font-weight: bold;">
	Available Balance is {{number_format($customerBalance,2)}} Tk
</span>
@else
<span style="color: red; font-weight: bold;">
	Available Balance is {{number_format($customerBalance,2)}} Tk
</span>
@endif