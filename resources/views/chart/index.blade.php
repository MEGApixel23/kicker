@extends('layouts.master')

@section('title', 'Chart')
@section('content')
    <div class="row">
        <div class="col-md-8 col-md-offset-2 content-wrap">
            <h3 class="text-center">User's chart</h3>
            <table class="table table-bordered table-condensed">
                <thead>
                <tr>
                    <th>#</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Rating</th>
                    <th>Games played</th>
                    <th>Games lost</th>
                    <th>Games won</th>
                    <th>Games draw</th>
                </tr>
                </thead>
            @foreach ($users as $user)
                <tr>
                    <td>{{ $i = isset($i) ? ++$i : 1 }}</td>
                    <td>{{ $user->name }}</td>
                    <td>{{ $user->email }}</td>
                    <td>{{ $user->rating }}</td>
                    <td>{{ $user->getCountGames() }}</td>
                    <td>{{ $user->count_looses }}</td>
                    <td>{{ $user->count_wins }}</td>
                    <td>{{ $user->count_draws }}</td>
                </tr>
            @endforeach
            </table>
        </div>
    </div>
@stop