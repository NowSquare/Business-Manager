<?php

/*
|--------------------------------------------------------------------------
| Globals
|--------------------------------------------------------------------------
*/

// String for REGEX with all available languages, except EN as that's the default language
$languages = implode('|', array_keys(array_except(config('system.available_languages'), [config('system.default_language')])));

// Reset demo
if (config('app.demo')) {
  Route::get('reset/{app_key}', '\Platform\Controllers\UpdateController@resetInstallation');
}

/*
 |--------------------------------------------------------------------------
 | Front-end
 |--------------------------------------------------------------------------
 */

Route::get('/', '\Platform\Controllers\Front\WebsiteController@getIndex')->name('index');
Route::get('terms', '\Platform\Controllers\Front\WebsiteController@getTerms')->name('terms');

Route::get('install', '\Platform\Controllers\InstallationController@getInstall')->name('installation');
Route::post('install', '\Platform\Controllers\InstallationController@postInstall');

/*
 |--------------------------------------------------------------------------
 | Assets, JavaScript variables and translations
 |--------------------------------------------------------------------------
 */

Route::get('assets/javascript', '\Platform\Controllers\AssetController@getJavascript');

/*
 |--------------------------------------------------------------------------
 | Dashboard
 |--------------------------------------------------------------------------
 */

// Secured routes (no verification required)
Route::group(['middleware' => 'auth'], function () {

  // Verify email address
  Route::get('verification-required', '\Platform\Controllers\App\VerificationController@getVerificationRequired')->name('verify');

});

// Secured routes with verified required
Route::group(['middleware' => ['auth' ,'verified']], function () {

  // Dashboard
  Route::get('dashboard', '\Platform\Controllers\App\DashboardController@getDashboard')->name('dashboard');

  // Profile
  Route::get('profile', '\Platform\Controllers\App\UserController@getProfile')->middleware('permission:access-profile')->name('profile');
  Route::post('profile', '\Platform\Controllers\App\UserController@postProfile')->middleware('permission:access-profile');

  // Settings
  Route::get('settings', '\Platform\Controllers\App\SettingController@getSettings')->middleware('permission:access-settings')->name('settings');
  Route::post('settings', '\Platform\Controllers\App\SettingController@postSettings')->middleware('permission:access-settings');
  Route::get('settings/tax-rates/json', '\Platform\Controllers\App\SettingController@getTaxRatesListJson')->middleware('permission:access-settings');
  Route::post('settings/tax-rates/delete', '\Platform\Controllers\App\SettingController@postDeleteTaxRates')->middleware('permission:access-settings');
  Route::post('settings/tax-rates/create', '\Platform\Controllers\App\SettingController@postCreateTaxRate')->middleware('permission:access-settings');
  Route::post('settings/run-migrations', '\Platform\Controllers\App\SettingController@postRunMigrations')->middleware('permission:access-settings');

  // Uploads
  Route::get('uploads', '\Platform\Controllers\App\FileManagerController@getUploads')->middleware('role:Admin')->name('uploads');

  // Users
  Route::get('users', '\Platform\Controllers\App\UserController@getUserList')->middleware('permission:list-users')->name('users');
  Route::get('users/view/{sl}', '\Platform\Controllers\App\UserController@getViewUser')->middleware('permission:view-user')->name('users');
  Route::get('users/create', '\Platform\Controllers\App\UserController@getCreateUser')->middleware('permission:create-user')->name('users');
  Route::get('users/import', '\Platform\Controllers\App\UserController@getImportUsers')->middleware('permission:create-user')->name('users');
  Route::post('users/import/parse-excel', '\Platform\Controllers\App\UserController@postParseExcel')->middleware('permission:create-user');
  Route::post('users/import/data', '\Platform\Controllers\App\UserController@postImport')->middleware('permission:create-user');
  Route::get('users/import/download-example', '\Platform\Controllers\App\UserController@getDownloadExampleExcel')->middleware('permission:create-user');
  Route::post('users/create', '\Platform\Controllers\App\UserController@postCreateUser')->middleware('permission:create-user')->name('users');
  Route::get('users/edit/{sl}', '\Platform\Controllers\App\UserController@getEditUser')->middleware('permission:edit-user')->name('users');
  Route::post('users/edit/{sl}', '\Platform\Controllers\App\UserController@postEditUser')->middleware('permission:edit-user')->name('users');
  Route::get('users/json', '\Platform\Controllers\App\UserController@getUserListJson')->middleware('permission:list-users');
  Route::get('users/export/{type}', '\Platform\Controllers\App\UserController@getExportRecords')->middleware('permission:list-users');
  Route::post('users/delete-users', '\Platform\Controllers\App\UserController@postDeleteUsers')->middleware('permission:delete-user');
  Route::get('users/login/{sl}', '\Platform\Controllers\App\UserController@getLoginAsUser')->middleware('permission:login-as-user');

  // Companies
  Route::get('companies', '\Platform\Controllers\App\CompanyController@getCompanyList')->middleware('permission:list-companies')->name('companies');
  Route::get('companies/view/{sl}', '\Platform\Controllers\App\CompanyController@getViewCompany')->middleware('permission:view-company')->name('companies');
  Route::get('companies/create', '\Platform\Controllers\App\CompanyController@getCreateCompany')->middleware('permission:create-company')->name('companies');
  Route::post('companies/create', '\Platform\Controllers\App\CompanyController@postCreateCompany')->middleware('permission:create-company')->name('companies');
  Route::get('companies/edit/{sl}', '\Platform\Controllers\App\CompanyController@getEditCompany')->middleware('permission:edit-company')->name('companies');
  Route::post('companies/edit/{sl}', '\Platform\Controllers\App\CompanyController@postEditCompany')->middleware('permission:edit-company')->name('companies');
  Route::get('companies/json', '\Platform\Controllers\App\CompanyController@getCompanyListJson')->middleware('permission:list-companies');
  Route::get('companies/export/{type}', '\Platform\Controllers\App\CompanyController@getExportRecords')->middleware('permission:list-companies');
  Route::post('companies/delete-companies', '\Platform\Controllers\App\CompanyController@postDeleteCompanies')->middleware('permission:delete-company');

  // Projects
  Route::get('projects', '\Platform\Controllers\App\ProjectController@getProjectList')->middleware('permission:list-projects')->name('projects');
  Route::get('projects/view/{sl}', '\Platform\Controllers\App\ProjectController@getViewProject')->middleware('permission:view-project')->name('projects');
  Route::get('projects/create', '\Platform\Controllers\App\ProjectController@getCreateProject')->middleware('permission:create-project')->name('projects');
  Route::post('projects/create', '\Platform\Controllers\App\ProjectController@postCreateProject')->middleware('permission:create-project')->name('projects');
  Route::get('projects/edit/{sl}', '\Platform\Controllers\App\ProjectController@getEditProject')->middleware('permission:edit-project')->name('projects');
  Route::post('projects/edit/{sl}', '\Platform\Controllers\App\ProjectController@postEditProject')->middleware('permission:edit-project')->name('projects');
  Route::get('projects/json', '\Platform\Controllers\App\ProjectController@getProjectListJson')->middleware('permission:list-projects');
  Route::get('projects/export/{type}', '\Platform\Controllers\App\ProjectController@getExportRecords')->middleware('permission:list-projects');
  Route::post('projects/delete-projects', '\Platform\Controllers\App\ProjectController@postDeleteProjects')->middleware('permission:delete-project');
  Route::post('projects/task/edit', '\Platform\Controllers\App\ProjectController@postEditTask')->middleware('permission:edit-project-task|mark-project-task-complete');
  Route::post('projects/proposition/approve', '\Platform\Controllers\App\ProjectController@postApproveProposition');
  Route::post('projects/proposition/reset-approval', '\Platform\Controllers\App\ProjectController@postResetPropositionApproval');
  Route::get('projects/pdf/proposition/{sl}', '\Platform\Controllers\App\ProjectController@getProjectProposition')->middleware('permission:view-project');

  // Invoices
  Route::get('invoices', '\Platform\Controllers\App\InvoiceController@getInvoiceList')->middleware('permission:list-invoices')->name('invoices');
  Route::get('invoices/create/{type?}/{sl?}/{var1?}/{var2?}', '\Platform\Controllers\App\InvoiceController@getCreateInvoice')->middleware('permission:create-invoice')->name('invoices');
  Route::post('invoices/create', '\Platform\Controllers\App\InvoiceController@postCreateInvoice')->middleware('permission:create-invoice')->name('invoices');
  Route::get('invoices/edit/{sl}', '\Platform\Controllers\App\InvoiceController@getEditInvoice')->middleware('permission:edit-invoice')->name('invoices');
  Route::post('invoices/edit/{sl}', '\Platform\Controllers\App\InvoiceController@postEditInvoice')->middleware('permission:edit-invoice')->name('invoices');
  Route::post('invoices/status', '\Platform\Controllers\App\InvoiceController@postChangeInvoiceStatus')->middleware('permission:edit-invoice');
  Route::post('invoices/send', '\Platform\Controllers\App\InvoiceController@postSendInvoice')->middleware('permission:edit-invoice');
  Route::get('invoices/json', '\Platform\Controllers\App\InvoiceController@getInvoiceListJson')->middleware('permission:list-invoices');
  Route::get('invoices/export/{type}', '\Platform\Controllers\App\InvoiceController@getExportRecords')->middleware('permission:list-invoices');
  Route::post('invoices/delete-invoices', '\Platform\Controllers\App\InvoiceController@postDeleteInvoices')->middleware('permission:delete-invoice');
  Route::get('invoices/pdf/{sl}', '\Platform\Controllers\App\InvoiceController@getInvoicePdf')->middleware('permission:view-invoice');

  // Comments
  Route::post('comments/add', '\Platform\Controllers\App\CommentController@postAddComment')->middleware('permission:create-comment');

  // Notifications
  Route::get('notifications', '\Platform\Controllers\App\NotificationController@getNotifications')->name('notifications');

});

/*
 |--------------------------------------------------------------------------
 | Auth routes
 |--------------------------------------------------------------------------
 */

$optionalLanguageRoutes = function() {
  // Authentication Routes
  Route::get('login', 'Auth\LoginController@showLoginForm')->name('login');
  Route::post('login', 'Auth\LoginController@login');
  Route::post('logout', 'Auth\LoginController@logout')->name('logout');
  Route::get('logout', '\App\Http\Controllers\Auth\LoginController@logout');

  // Registration Routes
  if (\Schema::hasTable('settings')) {
    $system_signup = \Platform\Controllers\Core\Settings::get('system_signup', 'boolean', 1);
  } else {
    $system_signup = 1;
  }

  if ($system_signup === 1) {
    Route::get('register', 'Auth\RegisterController@showRegistrationForm')->name('register');
    Route::post('register', 'Auth\RegisterController@register');
  }

  // Email Verification Routes
  Route::get('email/verify/{id}', '\Platform\Controllers\App\VerificationController@verify')->name('verification.verify');
  Route::get('email/resend', '\Platform\Controllers\App\VerificationController@resend')->name('verification.resend');

  // Password Reset Routes
  Route::get('password/reset', 'Auth\ForgotPasswordController@showLinkRequestForm')->name('password.request');
  Route::post('password/email', 'Auth\ForgotPasswordController@sendResetLinkEmail')->name('password.email');
  Route::get('password/reset/{token}', 'Auth\ResetPasswordController@showResetForm')->name('password.reset');
  Route::post('password/reset', 'Auth\ResetPasswordController@reset');
};

if ($languages != '') {
  // Add routes with language-prefix
  Route::group(['prefix' => '{language?}', 'where' => ['language' => '[' . $languages . ']+']], $optionalLanguageRoutes);
}

// Always add routes without prefix for default language
$optionalLanguageRoutes();
