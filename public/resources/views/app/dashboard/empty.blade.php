@extends('../../layouts.app')

@section('page_title', trans('g.dashboard') . ' - ' . config('system.name'))

@section('content')
  <div class="my-3 my-md-5">
    <div class="container">
      <div class="page-header">
        <h1 class="page-title">
          Welcome {{ auth()->user()->name }}!
        </h1>
      </div>

    </div>
  </div>
@stop

@section('page_bottom')

@stop