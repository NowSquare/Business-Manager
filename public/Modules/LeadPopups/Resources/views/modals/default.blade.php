@extends('leadpopups::modals.layout.master', ['id' => $id])

@section('head_end')
<style type="text/css">
  body {
    margin: 2rem;
  }
</style>
@stop

@section('content')

{!! $content !!}

<?php if (isset($success) && $success !== null) { ?>

<div class="alert alert-success rounded-0">
  {!! $success !!}
</div>

<?php } elseif (isset($error) && $error !== null) { ?>

<div class="alert alert-danger rounded-0">
  {!! $error !!}
</div>

<?php } else { ?>

{!! form($form, $formOptions = []) !!}

<?php } ?>

@stop