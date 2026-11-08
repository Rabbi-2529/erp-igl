@extends('layout.master')
@section('payroll_class','menu-open')
@section('office_holiday','active')
@section('content')
<section class="content">
        <section class="content-header">
            @if(session()->has('err'))
            <div class="alert alert-danger alert-dismissible" role="alert">
                {{ session('err') }}
              <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
              </button>
            </div>
            @endif
            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>   
            @endif
            @include('layout.session_message')
            
              <h1>
                Weekend & Holidays
              </h1>       
            </section>
            <section class="content-header">
              <div class="box">
                @if($holiday)
                    <div class="box-header with-border">
                        <blockquote>
                            <p class="offday">Your official weekend is: <span style="color: red">{{ $holiday->weekend }}</span></p>
                        </blockquote>
                    </div>
                @endif
          
                  <div class="box-main with-border">
                      @if($holiday)
                      <div class="box-header with-border">
                        <p class="offday">You Can Update Your Current Weekend From Here:</p>
                        <mark>NB: For Selecting Multiple Weekends, Press and Hold The "Ctrl" Key And Select Weekends.</mark>
                      <br>
                      </div>
                      
                      @else
                          <h3 class="box-title">Add Weekend</h3>
                      @endif
          
                      {{Form::open(['route' => 'payroll.add-holiday', 'id' => 'form' , 'method' => 'post' , 'id' => 'weekend'])}}
                          @csrf
                          @if($holiday)
                          <div class="box-header with-border">
                            <div class="form-group" id="weekendSelect">
                                <select class="form-control" name="weekend[]" multiple>
                                    <option value="saturday" {{ str_contains($holiday->weekend, 'saturday') ? 'selected' : '' }}>Saturday</option>
                                    <option value="sunday" {{ str_contains($holiday->weekend, 'sunday') ? 'selected' : '' }}>Sunday</option>
                                    <option value="monday" {{ str_contains($holiday->weekend, 'monday') ? 'selected' : '' }}>Monday</option>
                                    <option value="tuesday" {{ str_contains($holiday->weekend, 'tuesday') ? 'selected' : '' }}>Tuesday
                                    </option>
                                    <option value="wednesday" {{ str_contains($holiday->weekend, 'wednesday') ? 'selected' : '' }}>Wednesday</option>
                                    <option value="thursday" {{ str_contains($holiday->weekend, 'thursday') ? 'selected' : '' }}>Thursday</option>
                                    <option value="friday" {{ str_contains($holiday->weekend, 'friday') ? 'selected' : '' }}>Friday</option>
                                </select>
                            </div>
                            <button type="submit" class="btn btn-primary" id="saveBtn">Save</button>
                          </div>
                          @else
                              <mark>NB: For Selecting Multiple Weekends, Press and Hold The "Ctrl" Key And Select Weekends.</mark>
                              <div class="form-group" id="weekendSelect">
                                <br>
                                  <select class="form-control" name="weekend[]" multiple>
                                      <option value="saturday">Saturday</option>
                                      <option value="sunday">Sunday</option>
                                      <option value="monday">Monday</option>
                                      <option value="tuesday">Tuesday</option>
                                      <option value="wednesday">Wednesday</option>
                                      <option value="thursday">Thursday</option>
                                      <option value="friday">Friday</option>
                                  </select>
                              </div>
                              <button type="submit" class="btn btn-primary" id="saveBtn">Save</button>
                          @endif
                      {{ Form::close() }}
                  </div>
              </div>
          </section>
          
@endsection

<style>
  .form-group > select {
        height: 150px !important;
    }
  .form-control>option {
    font-feature-settings: "smcp" on;
    border-bottom: 1px solid black;
    }
  .center {
      display: flex;
      justify-content: center;
  }
  .form-group {
      text-align: center;
  }
  button[type=update] {
      margin: auto;
      display: block;
  }
  .offday{
    font-variant: small-caps;
  }
</style>



@section('custom_script')

<script>

const updateBtn = document.getElementById("updateBtn");
console.log(updateBtn);
  

</script>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/js/bootstrap.min.js"></script>
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.9.0/moment.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datetimepicker/4.17.37/js/bootstrap-datetimepicker.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.12.2/js/bootstrap-select.min.js"></script>


@endsection