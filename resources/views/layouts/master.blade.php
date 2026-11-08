<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        @include('layouts.head')
    </head>

    {{-- "fixed": header and sidebar stay in place; the sidebar scrolls on its own --}}
    <body class="hold-transition sidebar-mini fixed">

        <div class="page-loader-wrapper">
            <div class="loader">
                <div class="preloader">
                    <div class="spinner-layer pl-green">
                        <div class="circle-clipper left">
                            <div class="circle"></div>
                        </div>
                        <div class="circle-clipper right">
                            <div class="circle"></div>
                        </div>
                    </div>
                </div>
                <p>IGL ERP Solution...</p>
            </div>
        </div>

        <div class="wrapper">

            <header class="main-header">
                @include('layouts.header')
            </header>
            
            <aside class="main-sidebar">

                @if(auth()->user()->au_user_type == 1)
                    @include('layouts.sidebar_root')
                
                @elseif(auth()->user()->au_user_type == 3)
                    @include('layouts.sidebar_company')
       
                @elseif(auth()->user()->au_user_type == 4)
                    @include('layouts.sidebar_admin')
                @else
                    @include('layouts.sidebar_user')
                @endif

            </aside>
            <div class="content-wrapper">
                <br>
                <div class="content">
                    @include('layouts.message')
                    <div class="se-pre-con"></div>
                    @yield('content')
                </div>
            </div>
            <footer class="main-footer">
                <input type="hidden" name="" id="base_url" value="">

                <div class="pull-right hidden-xs">
                    Version 3.0
                </div>

                <strong>
                    Copyright © 2023
                </strong>
                    <a href="https://iglweb.com/" target="_blank">
                     IGL Web</a>
                <input type="hidden" id="base_url" value="" name="">
                <input type="hidden" name="dis_type" id="discount_type" value="">
                <input type ="hidden" name="csrf_test_name" id="CSRF_TOKEN" value="">
                <input type="hidden" id="currency" value="" name="">
            </footer>
        </div>
        
        @include('layouts.password_modal')
        @include('layouts.js')
           @yield('custom_script')
        <!-- calculator modal -->
        {{-- <div class="modal fade-scale" id="calculator" role="dialog">
            <div class="modal-dialog" id="calculatorcontent">
                <!-- Modal content-->
                <div class="modal-content">     
                    <div class="modal-body">
                        <div class="calcontainer">

                            <div class="screen">
                                <h1 id="mainScreen">0</h1>
                            </div>

                            <table class="cal-table">
                                <tr>
                                    <td><button value="7" id="7" class="cal-btn" onclick="InputSymbol(7)">7</button></td>
                                    <td><button value="8" id="8" class="cal-btn" onclick="InputSymbol(8)">8</button></td>
                                    <td><button value="9" id="9" class="cal-btn" onclick="InputSymbol(9)">9</button></td>
                                    <td><button onclick="DeleteLastSymbol()" class="cal-btn">CE</button></td>
                                </tr>
                                <tr>
                                    <td><button value="4" id="4" class="cal-btn" onclick="InputSymbol(4)">4</button></td>
                                    <td><button value="5" id="5" class="cal-btn" onclick="InputSymbol(5)">5</button></td>
                                    <td><button value="6" id="6" class="cal-btn" onclick="InputSymbol(6)">6</button></td>
                                    <td><button value="/" id="104" class="cal-btn" onclick="InputSymbol(104)">/</button></td>
                                </tr>
                                <tr>
                                    <td><button value="1" id="1" class="cal-btn" onclick="InputSymbol(1)">1</button></td>
                                    <td><button value="2" id="2" class="cal-btn" onclick="InputSymbol(2)">2</button></td>
                                    <td><button value="3" id="3" class="cal-btn" onclick="InputSymbol(3)">3</button></td>
                                    <td><button value="*" id="103" class="cal-btn" onclick="InputSymbol(103)">*</button></td>
                                </tr>
                                <tr>
                                    <td><button value="0" id="0" class="cal-btn" onclick="InputSymbol(0)">0</button></td>
                                    <td><button value="." id="128" class="cal-btn" onclick="InputSymbol(128)">.</button></td>
                                    <td><button value="-" id="102" class="cal-btn" onclick="InputSymbol(102)">-</button></td>
                                    <td><button value="+" id="101" class="cal-btn" onclick="InputSymbol(101)">+</button></td>
                                </tr>
                                <tr>
                                    <td colspan="2"><button onclick="ClearScreen()" class="cal-btn">C</button></td>
                                    <td colspan="1"><button onclick="CalculateTotal()" class="cal-btn">=</button></td>
                                    <td colspan="1"><button  data-dismiss="modal" class="cal-btn-danger"><i class="fa fa-power-off"></i></button></td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div> --}}
    </body>
</html>