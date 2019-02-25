@extends('../../layouts.app')

@section('page_title', trans('g.uploads') . ' - ' . config('system.name'))

@section('page_head')

  @include('layouts.modules.elfinder-init')

@stop

@section('content')
  <div class="my-3 my-md-5">
    <div class="container">
      <div class="row">
        <div class="col-12">
          <div class="card shadow-lg">
            <div class="card-header">
              <h3 class="card-title">{{ trans('g.uploads') }}</h3>
              <div class="card-options">
                {{ trans('g.uploads_info') }}
              </div>
            </div>
            <div class="card-body p-0">

              <div id="elfinder"></div>

            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
@stop

@section('page_bottom')
<script>
$(function() {
  fitFileManager();

  $(window).resize(fitFileManager);

  function fitFileManager() {
    $('#elfinder').css('height', (parseInt($(window).height()) - 314) + 'px');
    $('#elfinder').trigger('resize');
  }
});
</script>
@stop