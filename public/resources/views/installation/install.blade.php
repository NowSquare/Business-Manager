@extends('../../layouts.auth')

@section('page_title', 'Installation - ' . config('system.name'))

@section('content')

<div id="page_loader" class="dimmer active" style="position: fixed; left:0; top: 0; right: 0; bottom: 0; background-color: rgba(255,255,255,0.85); z-index: 999999; display: none">
  <div class="loader"></div>
  <div style="margin: 2.5rem 0 0 0; position: absolute; top: 50%; text-align: center; width: 100%" class="text-muted">Please be patient, installation may take a few minutes.<br>You will be redirected to the login page when finished.</div>
</div>

<div class="container">
  <div class="row">
    <div class="col mx-auto mt-5" style="max-width: 540px">
      <div class="text-center mb-6">
         <a class="header-brand" href="javscript:void(0);">
          <img src="{{ $system_icon }}" class="h-6 mb-1 mr-1" alt="{{ config('system.name') }}">
          {{ config('system.name') }} Installation
        </a>
      </div>

      <form class="card" action="{{ url('install') }}" method="post" id="frmInstall">
        <div class="card-body pt-0 pb-3 px-3">

          <ul class="nav nav-tabs" role="tablist">
            <li class="nav-item pl-5">
              <a class="nav-link active" id="domain-tab" data-toggle="tab" href="#domain" role="tab" aria-selected="true">Domain</a>
            </li>
            <li class="nav-item">
              <a class="nav-link" id="database-tab" data-toggle="tab" href="#database" role="tab" aria-selected="false">Database</a>
            </li>
            <li class="nav-item">
              <a class="nav-link" id="email-tab" data-toggle="tab" href="#email" role="tab" aria-selected="false">Email</a>
            </li>
            <li class="nav-item">
              <a class="nav-link" id="business-tab" data-toggle="tab" href="#business" role="tab" aria-selected="false">Business</a>
            </li>
            <li class="nav-item">
              <a class="nav-link" id="login-tab" data-toggle="tab" href="#login" role="tab" aria-selected="false"> Login</a>
            </li>
          </ul>


          <div class="tab-content">
            <div class="tab-pane fade show active px-3 pt-5" id="domain" role="tabpanel" aria-labelledby="domain-tab">

              <div class="form-group">
                <label for="APP_NAME" class="form-label required">Platform name</label>
                <input id="APP_NAME" name="APP_NAME" type="text" placeholder="Wonka Industries" maxlength="32" value="Business Manager" required class="form-control">
              </div>

              <div class="form-group">
                <label for="APP_URL" class="form-label required">Platform url</label>
                  <input id="APP_URL" type="text" class="form-control" name="APP_URL" required value="{{ \Request::getSchemeAndHttpHost() }}" placeholder="">
              </div>

            </div>
            <div class="tab-pane fade px-3 pt-5" id="database" role="tabpanel" aria-labelledby="database-tab">

              <p class="text-muted">Currently only MySQL is supported.</p>

              <div class="row">
                <div class="col-sm-8">
                  <div class="form-group">
                    <label for="DB_HOST" class="form-label required">Host</label>
                    <input id="DB_HOST" name="DB_HOST" type="text" placeholder="127.0.0.1" value="127.0.0.1" maxlength="32" class="form-control" required>
                  </div>
                </div>
                <div class="col-sm-4">
                  <div class="form-group">
                    <label for="DB_PORT" class="form-label required">Port</label>
                    <input id="DB_PORT" name="DB_PORT" type="text" placeholder="3306" value="3306" maxlength="10" class="form-control" required>
                  </div>
                </div>
              </div>

              <div class="form-group">
                <label for="DB_DATABASE" class="form-label required">Database name</label>
                <input id="DB_DATABASE" name="DB_DATABASE" type="text" placeholder="" value="" class="form-control" required>
                <small class="form-text text-muted">Make sure the database is empty, or installation may fail.</small>
              </div>

              <div class="form-group">
                <label for="DB_USERNAME" class="form-label required">Username</label>
                <input id="DB_USERNAME" name="DB_USERNAME" type="text" placeholder="" value="" class="form-control" required>
              </div>

              <div class="form-group">
                <label for="DB_PASSWORD" class="form-label">Password</label>
                <input id="DB_PASSWORD" name="DB_PASSWORD" type="text" placeholder="" class="form-control">
              </div>
              
            </div>
            <div class="tab-pane fade px-3 pt-5" id="email" role="tabpanel" aria-labelledby="email-tab">

              <p class="text-muted">If you want to use another mail driver (like <strong>smtp</strong> or <strong>mailgun</strong>), you can edit these settings after installation in the <code>.env</code> file in the web root.</p>

              <div class="form-group">
                <label for="MAIL_DRIVER" class="form-label required">Driver</label>
                <select id="MAIL_DRIVER" name="MAIL_DRIVER" class="form-control selectize" required>
                  <option value="mail">mail</option>
                  <option value="sendmail">sendmail</option>
                </select>
              </div>

              <div class="form-group">
                <label for="MAIL_FROM_NAME" class="form-label required">Mail from name</label>
                <input id="MAIL_FROM_NAME" name="MAIL_FROM_NAME" type="text" placeholder="" value="Business Manager" class="form-control" required>
              </div>

              <div class="form-group">
                <label for="MAIL_FROM_ADDRESS" class="form-label required">Mail from email</label>
                <input id="MAIL_FROM_ADDRESS" name="MAIL_FROM_ADDRESS" type="email" placeholder="noreply@example.com" value="{{ 'noreply@' . \Request::getHttpHost() }}" class="form-control" required>
              </div>

            </div>
            <div class="tab-pane fade px-3 pt-5" id="business" role="tabpanel" aria-labelledby="business-tab">

              <div class="form-group">
                <label for="company_name" class="form-label required">Company name</label>
                <input id="company_name" name="company_name" type="text" minlength="2" maxlength="64" placeholder="Acme Corp" value="" class="form-control" required>
              </div>

              <div class="form-group">
                <label for="company_email" class="form-label">Email address</label>
                <input id="company_email" name="company_email" type="email" placeholder="" class="form-control">
              </div>

              <div class="form-group">
                <label for="company_phone" class="form-label">Phone</label>
                <input id="company_phone" name="company_phone" type="text" placeholder="" maxlength="32" class="form-control">
              </div>

            </div>
            <div class="tab-pane fade px-3 pt-5" id="login" role="tabpanel" aria-labelledby="login-tab">

              <p class="text-muted">You can login with this user after installation. Make sure you don't forget the password.</p>

              <div class="form-group">
                <label for="name" class="form-label required">Full name</label>
                <input id="name" name="name" type="text" placeholder="" minlength="2" maxlength="32" value="System Owner" class="form-control" required>
              </div>

              <div class="form-group">
                <label for="email" class="form-label required">Email address</label>
                <input id="email" name="email" type="email" placeholder="" class="form-control" required>
              </div>

              <div class="form-group">
                <label for="phone" class="form-label">Phone</label>
                <input id="phone" name="phone" type="text" placeholder="" maxlength="32" class="form-control">
              </div>

              <label for="pass" class="form-label required">Password</label>
              <div class="row gutters-xs mb-4">
                <div class="col">
                  <div class="input-icon">
                    <span class="input-icon-addon">
                      <i class="material-icons" style="font-size:16px">lock</i>
                    </span>
                    <input id="pass" name="pass" type="password" minlength="6" maxlength="32" class="form-control" required>
                  </div>
                </div>
                <span class="col-auto">
                  <button id="toggle_pass" class="btn btn-secondary" type="button" data-toggle="tooltip" title="<?php echo trans('g.show_password') ?>"><i class="fe fe-eye"></i></button>
                </span>
                <span class="col-auto">
                  <button onclick="$('#pass').val(randomString(8));" class="btn btn-secondary" type="button" data-toggle="tooltip" title="<?php echo trans('g.generate_password') ?>"><i class="fe fe-refresh-cw"></i></button>
                </span>
              </div>

              <br>
              <div class="alert alert-info rounded-0">
                After installation you can change configuration settings in the <code>.env</code> file in the web root.
              </div>

            </div>
          </div>

          <div class="form-footer">
            <div class="mx-3 mb-3">
              <button type="submit" class="btn btn-outline-primary btn-block" id="submit_form_with_tabs">Install</button>
            </div>
          </div>
        </div>
      </form>
    </div>
  </div>
</div>
@stop

@section('page_bottom')
<script>
$(function() {

  $('#frmInstall').on('submit', function() {
    $('#page_loader').fadeIn();
  });

  $('#toggle_pass').on('click', function() {
    if(! $(this).hasClass('active')) {
      $(this).addClass('active');
      $(this).find('i').removeClass('fe-eye').addClass('fe-eye-off');
      $(this).attr('data-original-title', "<?php echo trans('g.hide_password') ?>").tooltip('show');
      togglePasswordField('pass', 'form-control', true);
    } else {
      $(this).removeClass('active');
      $(this).find('i').removeClass('fe-eye-off').addClass('fe-eye');
      $(this).attr('data-original-title', "<?php echo trans('g.show_password') ?>").tooltip('show');
      togglePasswordField('pass', 'form-control', false);
    }
  });
});
</script>
@stop