      <footer class="footer">
        <div class="container">
          <div class="row align-items-center flex-row-reverse">
            <div class="col-auto ml-lg-auto">
              <div class="row align-items-center">
                <div class="col-auto">
                  <ul class="list-inline list-inline-dots mb-0">
                    <li class="list-inline-item"><a href="{{ url('terms') }}">{{ trans('g.terms_and_policy') }}</a></li>
                  </ul>
                </div>
              </div>
            </div>
            <div class="col-12 col-lg-auto mt-3 mt-lg-0 text-center">
              {{ trans('g.copyright_info', ['year' => date('Y'), 'system_name' => config('system.name')]) }}
            </div>
          </div>
        </div>
      </footer>