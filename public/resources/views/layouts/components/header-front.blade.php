        <div class="header py-4">
          <div class="container">
            <div class="d-flex">
              <a class="header-brand" href="{{ url('/') }}">
                <img src="{{ $system_icon }}" class="header-brand-img" alt="{{ config('system.name') }}">
                {{ config('system.name') }}
              </a>
              <div class="d-flex order-lg-2 ml-auto">
                <div class="nav-item d-md-flex">
<?php if (auth()->check()) { ?>
                  <a href="{{ url('dashboard') }}" class="btn btn-sm btn-outline-primary">{{ trans('g.dashboard') }}</a>
<?php } else { ?>
                  <a href="{{ url('login') }}" class="btn btn-sm btn-outline-primary">{{ trans('g.login') }}</a>
<?php } ?>
                </div>
              </div>
              <a href="#" class="header-toggler d-lg-none ml-3 ml-lg-0" data-toggle="collapse" data-target="#headerMenuCollapse">
                <span class="header-toggler-icon"></span>
              </a>
            </div>
          </div>
        </div>