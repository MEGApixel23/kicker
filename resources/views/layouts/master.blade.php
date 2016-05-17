<!DOCTYPE html>
<html ng-app="kickerApp">
    <head>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>@yield('title')</title>

        <link rel="shortcut icon" href="{{ asset('favicon.ico') }}" type="image/x-icon">

        {{-- Roboto font on Google fonts --}}
        <link href='https://fonts.googleapis.com/css?family=Roboto:400,300&subset=latin,cyrillic' rel='stylesheet' type='text/css'>

        {{-- Font awesome CDN --}}
        <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.5.0/css/font-awesome.min.css">

        {{ Html::style('css/main.css') }}
        @yield('styles')
    </head>
    <body>
        <nav class="navbar navbar-default navbar-fixed-top navbar-center">
            <div class="container">
                <div class="navbar-header">
                    <button type="button" class="navbar-toggle collapsed" data-toggle="collapse"
                            data-target="#bs-example-navbar-collapse-6" aria-expanded="false"><span class="sr-only">Toggle navigation</span>
                        <span class="icon-bar"></span> <span class="icon-bar"></span> <span class="icon-bar"></span>
                    </button>
                </div>
                <div class="collapse navbar-collapse" id="bs-example-navbar-collapse-6">
                    <ul class="nav navbar-nav navbar-center">
                        <li><a href="/#/">Games</a></li>
                        <li><a href="/#/chart">Chart</a></li>
                        @if(!Auth::check())
                            <li><a href="/#/signin">Login</a></li>
                            <li><a href="/#/signup">Register</a></li>
                        @else
                            <li><a href="/#/game/create">Add game</a></li>
                            <li><a href="/#/user/profile">Profile</a></li>
                            <li><a href="/logout">Logout</a></li>
                        @endif
                    </ul>
                </div>
            </div>
        </nav>
        <div class="container main-container">
            <div class="content">
                <div class="row games-container" ng-show="currentPlayer.deleted">
                    <div class="col-xs-12 col-sm-12 col-md-9 col-lg-6 game-container col-centered text-center">
                        Your profile is not active. You can <a href="javascript:void(0)" ng-click="restoreProfile()">
                            restore profile</a> to get full access.
                    </div>
                </div>

                <div ng-view></div>
            </div>
            <div class="clearfix"></div>
        </div>

        {{ Html::script('js/app.js') }}
        @yield('scripts')

    </body>
</html>
