@extends('../../layouts.app')

@section('page_title', trans('g.view_company') . ' - ' . $company->name  . ' - ' . config('system.name'))

@section('page_head')
  <link rel="stylesheet" href="{{ url('assets/css/wysiwyg.css?' . config('system.client_side_timestamp')) }}">
  <script src="{{ url('assets/js/wysiwyg.js?' . config('system.client_side_timestamp')) }}"></script>
@stop

@section('content')

  <div class="my-3 my-md-5">
    <div class="container">
      <div class="row">

        <div class="col-12">

          {!! form_start($form) !!}

          <div class="card">
            <div class="card-header">
              <h3 class="card-title">{{ $company->active_name }}</h3>
            </div>

            <div class="card-body p-0 px-3">

              <ul class="nav nav-tabs" role="tablist">
                <li class="nav-item pl-5">
                  <a class="nav-link active" id="contact-tab" data-toggle="tab" href="#contact" role="tab" aria-selected="true">{{ trans('g.details') }}</a>
                </li>
              </ul>

              <div class="tab-content">
                <div class="tab-pane show active px-3 pb-3" id="contact" role="tabpanel" aria-labelledby="contact-tab">
                  <div class="row">
                    <div class="col-md-6 col-lg-4">
                      {!! form_until($form, 'logo') !!}
                    </div>
                    <div class="col-md-6 col-lg-4">
                      {!! form_until($form, 'country_code') !!}
                    </div>
                    <div class="col-md-6 col-lg-4">
                      {!! form_until($form, 'fax') !!}
                    </div>
                  </div>
                </div>
              </div>

            </div>

            <div class="card-footer text-right">
                <button class="btn btn-secondary mr-1" type="button" onclick="document.location = '{{ url('companies') }}';">{{ trans('g.back') }}</button>
<?php if (auth()->user()->can('edit-company') && $can_access_company) { ?>
              <a href="{{ url('companies/edit/' . $sl) }}" class="btn btn-primary tab-hash">{{ trans('g.edit_company') }}</a>
<?php } ?>
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