@extends('../../layouts.app')

@section('page_title', trans('g.profile') . ' - ' . config('system.name'))

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

          @if(session()->has('warning'))
          <div class="alert alert-warning rounded-0">
            {!! session()->get('warning') !!}
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
              <h3 class="card-title">{{ trans('g.profile') }}</h3>
            </div>
            <div class="card-body p-0 px-3">

              <ul class="nav nav-tabs" role="tablist">
                <li class="nav-item pl-5">
                  <a class="nav-link" id="account-tab" data-toggle="tab" href="#account" role="tab" aria-selected="false">{{ trans('g.account') }}</a>
                </li>
                <li class="nav-item">
                  <a class="nav-link" id="contact-tab" data-toggle="tab" href="#contact" role="tab" aria-selected="false">{{ trans('g.contact_details') }}</a>
                </li>
                <li class="nav-item">
                  <a class="nav-link" id="localization-tab" data-toggle="tab" href="#localization" role="tab" aria-selected="false">{{ trans('g.localization') }}</a>
                </li>
              </ul>

              <div class="tab-content">
                <div class="tab-pane px-3 pb-3" id="account" role="tabpanel" aria-labelledby="account-tab">
                  <div class="row">
                    <div class="col-md-6 col-lg-5">
                      {!! form_until($form, 'password') !!}
                    </div>
                    <div class="col-md-6 col-lg-5 offset-lg-1">
                      {!! form_until($form, 'avatar') !!}
                    </div>
                  </div>
                </div>
                <div class="tab-pane px-3 pb-3" id="contact" role="tabpanel" aria-labelledby="contact-tab">
                  <div class="row">
                    <div class="col-md-6 col-lg-4">
                      {!! form_until($form, 'date_of_birth') !!}
                    </div>
                    <div class="col-md-6 col-lg-4">
                      {!! form_until($form, 'fax') !!}
                    </div>
                    <div class="col-md-6 col-lg-4">
                      {!! form_until($form, 'country_code') !!}
                    </div>
                  </div>
                </div>
                <div class="tab-pane px-3 pb-3" id="localization" role="tabpanel" aria-labelledby="localization-tab">
                  <div class="row">
                    <div class="col-md-6 col-lg-5">
                      {!! form_until($form, 'time_format') !!}
                    </div>
                    <div class="col-md-6 col-lg-5 offset-lg-1">
                      {!! form_until($form, 'seperators') !!}
                    </div>
                  </div>
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