@extends('../../layouts.app')

@section('page_title', trans('g.create_company') . ' - ' . config('system.name'))

@section('page_head')
  <link rel="stylesheet" href="{{ url('assets/css/wysiwyg.css?' . config('system.client_side_timestamp')) }}">
  <script src="{{ url('assets/js/wysiwyg.js?' . config('system.client_side_timestamp')) }}"></script>
@stop

@section('content')

  <div class="my-3 my-md-5">
    <div class="container">
      <div class="row">

        <div class="col-12">

          @if(session()->has('success'))
          <div class="alert alert-success rounded-0">
            {!! session()->get('success') !!}
          </div>
          @endif

          @if ($errors->any())
          <div class="alert alert-danger rounded-0">
            {!! trans('g.form_error') !!}
          </div>
          @endif

          {!! form_start($form) !!}

          <div class="card">
            <div class="card-header">
              <h3 class="card-title">{{ trans('g.create_company') }}</h3>
            </div>

            <div class="card-body p-0 px-3">

              <ul class="nav nav-tabs" role="tablist">
                <li class="nav-item pl-5">
                  <a class="nav-link active" id="contact-tab" data-toggle="tab" href="#contact" role="tab" aria-selected="true">{{ trans('g.details') }}</a>
                </li>
                <li class="nav-item">
                  <a class="nav-link" id="notes-tab" data-toggle="tab" href="#company-notes" role="tab" aria-selected="false">{{ trans('g.notes') }}</a>
                </li>
              </ul>

              <div class="tab-content">
                <div class="tab-pane fade show active px-3 pb-3" id="contact" role="tabpanel" aria-labelledby="contact-tab">
                  <div class="row">
                    <div class="col-md-6 col-lg-4">
                      {!! form_until($form, 'active') !!}
                    </div>
                    <div class="col-md-6 col-lg-4">
                      {!! form_until($form, 'country_code') !!}
                    </div>
                    <div class="col-md-6 col-lg-4">
                      {!! form_until($form, 'fax') !!}
                    </div>
                  </div>
                </div>
                <div class="tab-pane fade py-3" id="company-notes" role="tabpanel" aria-labelledby="notes-tab">
                  {!! form_until($form, 'notes') !!}
                </div>
              </div>

            </div>

            <div class="card-footer text-right">
                {!! form_rest($form) !!}
            </div>
          </div>

          {!! form_end($form) !!}

        </div>

      </div>
    </div>
  </div>
@stop

@section('page_bottom')
@stop