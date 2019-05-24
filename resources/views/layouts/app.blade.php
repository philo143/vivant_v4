<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'SMART Energy App') }}</title>

    <!-- Styles -->

    <link href=" {{ asset('css/app.css') }} " rel="stylesheet">
    <link rel="stylesheet" href="//cdnjs.cloudflare.com/ajax/libs/jasny-bootstrap/3.1.3/css/jasny-bootstrap.min.css"> 

    <!-- Scripts -->
    {{-- <script src="https://unpkg.com/vue/dist/vue.js"></script> --}}

  
    <script>
        window.Laravel = <?php echo json_encode([
            'csrfToken' => csrf_token(),
        ]); ?>
    </script>
    <style> .bootbox.modal > .modal-dialog { z-index: 99999 !important; } 

    .bootbox-alert, .bootbox-prompt , .bootbox-confirm, .bootbox-dialog {
        z-index: 999999;
    }


    div.dataTables_info {
        font-family: "Open Sans", "Helvetica Neue", Helvetica, Arial, sans-serif;
        font-size :12px;

    }
    </style> <!-- to be added to app/css -->
</head>
<body>
    <div id="app">
        <div class="container-fluid" style="height: 80px;">
            <div class="row">
                <div class="col-lg-4"><a class="navbar-brand" href="#"><img src="{{ asset('img/VEC.jpg')}}" alt="" height="60px" width="250px"></a></div>
                <div class="col-lg-4"><br></div>
                <div class="col-lg-4"><br><span class="pull-right"></span></div>
            </div>
        </div>
        <nav class="navbar navbar-default">
            <div class="container-fluid">
                <div class="navbar-header">
                    <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1">
                        <span class="sr-only">Toggle navigation</span>
                        <span class="icon-bar"></span>
                        <span class="icon-bar"></span>
                        <span class="icon-bar"></span>
                    </button>
                    <a class="navbar-brand" href="#"></a>
                </div>

                <div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
                    <ul class="nav navbar-nav">
                        <li class="active"><a href="{{ route('dashboard') }}">Dashboard <span class="sr-only">(current)</span></a></li>
                        <li class="dropdown">
                            <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false">Market Analysis<span class="caret"></span></a>
                            <ul class="dropdown-menu" role="menu">
                                <li><a href="{{ route('dap_schedules.list') }}">MMS Data</a></li>
                                 <!-- <li><a href="{{ route('manual_downloader.rtd_lmp.index') }}">Manual Downloaders</a></li> -->
                            </ul>
                        </li>
                        <li class="dropdown">
                            <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false">Trading <span class="caret"></span></a>
                            <ul class="dropdown-menu" role="menu">
                                <li><a href="{{ route('scheduled_offer') }}">Bids and Offers</a></li>
                                <li><a href="{{ route('availability_report.list') }}">Plant Availability</a></li>
                                <li><a href="{{ route('realtime_plant_monitoring.tradingList') }}">Realtime Plant Monitoring</a></li>
                                <li><a href="{{ route('meter_data.mq_load.index') }}">Meter Data</a></li>
                               <li><a href="{{ route('trading_shift_report.list') }}">Shift Reports</a></li>
                                <li><a href="{{ route('aspa_nomination.input') }}">ASPA Nominations</a></li>
                            </ul>
                        </li>
                        <li class="dropdown">
                            <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false">Plant Operations <span class="caret"></span></a>
                            <ul class="dropdown-menu" role="menu">
                                <li><a href="{{ route('realtime_plant_capability.list') }}">Plant Capability</a></li>
                                <li><a href="{{ route('realtime_plant_monitoring.plantList') }}">Realtime Plant Monitoring</a></li>
                                <li><a href="{{ route('plant_shift_report.index') }}">Plant Operational Shift Report</a></li>
                            </ul>
                        </li>

                        <li><a href="{{ route('bcq.uploader.index') }}">BCQ</a></li>
                        <li class="dropdown">
                            <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false">Reserve <span class="caret"></span></a>
                            <ul class="dropdown-menu" role="menu">
                                <li><a href="{{ route('reserve_capability.create') }}">Reserve Capability Submission</a></li>
                                 <li><a href="{{ route('reserve_capability.list') }}">Reserve Capability History</a></li>
                                 <li><a href="{{ route('reserve_nomination.create') }}">Reserve Nomination Submission</a></li>
                                 <li><a href="{{ route('reserve_nomination.list') }}">Reserve Nomination History</a></li>
                                 <li><a href="{{ route('reserve_schedule.create') }}">Reserve Schedules</a></li>
                                 <li><a href="{{ route('reserve_schedule.list') }}">Reserve Schedule History</a></li>
                            </ul>
                        </li>
                        {{-- <li><a href="#">Billing & Settlements</a></li>
                        <li><a href="#">Sales</a></li> --}}


                        @if(Auth::check())
                            @if( Auth::user()->hasCustomers() == true)
                                   <li class="dropdown">
                                    <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false">Buyer/Customer<span class="caret"></span></a>
                                    <ul class="dropdown-menu" role="menu">
                                        <li><a href="{{ route('nomination.template') }}">Nominations</a></li>
                                    </ul>
                                </li>
                            @else 
                                @if( Auth::user()->hasRole('superadministrator') ||
                                    Auth::user()->hasRole('administrator') ||
                                    Auth::user()->hasRole('trader') )
                                     <li class="dropdown">
                                        <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false">Buyer/Customer<span class="caret"></span></a>
                                        <ul class="dropdown-menu" role="menu">
                                            <li><a href="{{ route('nomination.extraction_report') }}">Nominations</a></li>
                                        </ul>
                                    </li>
                                @endif
                            @endif
                        @endif

                        
                        <li class="dropdown">
                            <a href="{{ route('admin.index') }}" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false">Admin Tools <span class="caret"></span></a>
                            <ul class="dropdown-menu" role="menu">
                                <li><a href="{{ route('users.list') }}">Users</a></li>
                                <li><a href="{{ route('participants.list') }}">Participants</a></li>
                                <li><a href="{{ route('customers.list') }}">Customers</a></li>
                                {{-- <li><a href="#">Contracts</a></li>
                                <li><a href="#">Fuel Types</a></li> --}}
                                <li><a href="{{ route('dashboard.manage') }}">Dashboard</a></li>

                                @if ( Auth::check() ) 
                                    @if( Auth::user()->hasRole('superadministrator') )
                                          <li><a href="{{ route('resource_lookup.admin.list') }}">Settings</a></li>
                                          
                                    @endif
                                @endif 
                                
                               
                            </ul>
                        </li>
                    </ul>
                    <!-- Right Side Of Navbar --> 
                    <ul class="nav navbar-nav navbar-right">
                        <!-- Authentication Links -->
                        @if (Auth::guest())
                            <li><a href="{{ url('/login') }}">Login</a></li>
                            <!-- <li><a href="{{ url('/register') }}">Register</a></li> -->
                        @else
                            <li class="dropdown">
                                <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false">
                                    <i class="fa fa-user-circle"></i>&nbsp; {{ Auth::user()->username }} <span class="caret"></span>
                                </a>

                                <ul class="dropdown-menu" role="menu">
                                    <li><a href="{{ url('/settings/2fa') }}">Two Factor Authentication</a></li>
                                    <li><a href="{{ route('dashboard.settings') }}">Dashboard Settings</a></li>
                                    <li><a href="{{ route('password.form') }}">Change Password</a></li>
                                    <li class="divider"></li>
                                    <li>
                                        <a href="{{ url('/logout') }}"
                                            onclick="event.preventDefault();
                                                     document.getElementById('logout-form').submit();">
                                            Logout
                                        </a>

                                        <form id="logout-form" action="{{ url('/logout') }}" method="POST" style="display: none;">
                                            {{ csrf_field() }}
                                        </form>
                                    </li>
                                </ul>
                            </li>
                        @endif
                    </ul>
                </div>
            </div>
        </nav>
        
        {{-- <div class="container">
            <nav class="nav">
                <div class="nav-left">
                    <a class="nav-item">

                      <img src="{{ asset('img/smart-energy-logo.png') }}" alt="">
                    </a>
                </div>
              <!-- This "nav-toggle" hamburger menu is only visible on mobile -->
              <!-- You need JavaScript to toggle the "is-active" class on "nav-menu" -->
                <span class="nav-toggle">
                    <span></span>
                    <span></span>
                    <span></span>
                </span>

                <!-- This "nav-menu" is hidden on mobile -->
                <!-- Add the modifier "is-active" to display it on mobile -->
                <div class="nav-right nav-menu">
                    <a class="nav-item"><i class="fa fa-search"></i>&nbsp;Documentation</a>
                    
                    @if (Auth::guest())
                        <span class="nav-item">
                        <a class="button is-outlined is-primary" href="{{ url('/login') }}">Login</a>
                        </span>
                    @else
                        <a class="nav-item"><i class="fa fa-user-circle"></i>&nbsp;{{ Auth::user()->name }}</a>
                        <span class="nav-item">
                            <a class="button is-outlined is-danger"  @click="submitForm">Logout</a>
                        </span>
                        <form id="logout-form" action="{{ url('/logout') }}" method="POST" style="display: none;">
                        {{ csrf_field() }}
                        </form>
                    @endif

                </div>
            </nav>
        </div> --}}

            <br>
            @yield('content')    
        
    {{-- <div id="app">
        <nav class="navbar navbar-default navbar-static-top">
            <div class="container">
                <div class="navbar-header">

                    <!-- Collapsed Hamburger -->
                    <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#app-navbar-collapse">
                        <span class="sr-only">Toggle Navigation</span>
                        <span class="icon-bar"></span>
                        <span class="icon-bar"></span>
                        <span class="icon-bar"></span>
                    </button>

                    <!-- Branding Image -->
                    <a class="navbar-brand" href="{{ url('/') }}">
                        {{ config('app.name', 'Laravel') }}
                    </a>
                </div>

                <div class="collapse navbar-collapse" id="app-navbar-collapse">
                    <!-- Left Side Of Navbar -->
                    <ul class="nav navbar-nav">
                        &nbsp;
                    </ul>

                    <!-- Right Side Of Navbar -->
                    <ul class="nav navbar-nav navbar-right">
                        <!-- Authentication Links -->
                        @if (Auth::guest())
                            <li><a href="{{ url('/login') }}">Login</a></li>
                            <li><a href="{{ url('/register') }}">Register</a></li>
                        @else
                            <li class="dropdown">
                                <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false">
                                    {{ Auth::user()->name }} <span class="caret"></span>
                                </a>

                                <ul class="dropdown-menu" role="menu">
                                    <li>
                                        <a href="{{ url('/logout') }}"
                                            onclick="event.preventDefault();
                                                     document.getElementById('logout-form').submit();">
                                            Logout
                                        </a>

                                        <form id="logout-form" action="{{ url('/logout') }}" method="POST" style="display: none;">
                                            {{ csrf_field() }}
                                        </form>
                                    </li>
                                </ul>
                            </li>
                        @endif
                    </ul>
                </div>
            </div>
        </nav>

        @yield('content')
    </div> --}}
        <footer class="footer">
          <div class="container">
            <h6>
            <p class="text-center"> 
                <strong>SMART Energy Software Version 4</strong> by <a href="http://acacia-soft.com">Acaciasoft Corp</a>. The source code is licensed.
            </p>
            </h6>
          </div>
        </footer>
    </div>
    <!-- Scripts -->

    

    <script src="{{ asset('js/app.js') }}"></script>
    

    <!-- date picker -->
    <!-- Include Required Prerequisites -->
    <script type="text/javascript" src="//cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
    <script type="text/javascript" src="//cdn.jsdelivr.net/bootstrap.daterangepicker/2/daterangepicker.js"></script>
    <link rel="stylesheet" type="text/css" href="//cdn.jsdelivr.net/bootstrap.daterangepicker/2/daterangepicker.css" />
    <script src="//cdnjs.cloudflare.com/ajax/libs/jasny-bootstrap/3.1.3/js/jasny-bootstrap.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootbox.js/4.4.0/bootbox.min.js"></script>


    @yield('scripts')
    {{-- <script>
        Vue.component('task-list',{
            template: `
                <ul>
                    <task v-for="task in tasks">@{{ task.item }}</task>
                </ul>
            `,
            data() {
                return {
                    tasks: [
                        { item: "Dashboard", url: '#', class: '' },
                        { item: 'Trading', url: '#', class: '' },
                        { item: 'Market Analysis', url: '#', class: '' },
                        { item: 'Plant Operations', url: '#', class: '' },
                        { item: 'BCQ', url: '#', class: '' },
                        { item: 'Billing & Settlements', url: '#', class: '' },
                        { item: 'Sales', url: '#', class: '' },
                        { item: 'Buyer/Customer', url: '#', class: '' },
                        { item: 'Admin Tools', url: '{{ route('admin.index') }}', class: '' },
                        
                    ]
                };
            },

        });
        Vue.component('task', {
            template: '<li><a href="#"><slot></slot></a></li>'
        });
        var app = new Vue({
            el: '#app',
            methods: {
                submitForm: function() {
                    document.getElementById("logout-form").submit();
                }
            }
        });
    </script> --}}
    <script src="{{ asset('js/dataTables.fixedColumns.min.js') }}"></script>
    <script src="{{ asset('js/jquery.autoNumeric.js') }}"></script>
 {{--    <script src="{{ asset('js/main_socket_io.js') }}"></script> --}}

    <script type="text/javascript">
        $(document).ajaxError(function(event, jqxhr, settings, exception) {
                if (exception == 'Unauthorized') {
                    window.location = '/login';
                }
            });
        var nodejs_port = '{{env('NODEJS_PORT')}}';
        var protocol = '{{env('PROTOCOL')}}'.toLowerCase();
        var server = "{{ request()->root() }}";
        server = server.replace(/(:\d+)/g,'');
        if ( protocol == 'http' ) {
            // var socket = io(server+':'+nodejs_port);
            var socket = io(server+':'+nodejs_port);            
        }else {
            var url_server = server+':'+nodejs_port+'/';
            var socket = io.connect(url_server, {secure: true, reconnect: true, rejectUnauthorized : false});
            // var socket = io(server+':'+nodejs_port, { secure: true, reconnect: true, rejectUnauthorized : false });

            // var socket = io.connect(server, {secure: true, port: nodejs_port});
            // socket.on('connect', function(){
            //     socket.on('event', function(data){});
            //     socket.on('disconnect', function(){});
            //   });
        }

        // var socket = io(server+':'+nodejs_port);
        // console.log('server ' + server)
        
        // console.log(socket);
    </script>
</body>
</html>
