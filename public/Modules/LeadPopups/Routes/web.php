<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Public routes, cross-domain accessible
Route::group(['middleware' => 'cors', 'prefix' => 'popups'], function () {
  Route::get('get/{template?}/{id?}', 'ModalController@getModal');
  Route::post('get/{template?}/{id?}', 'ModalController@postModal');
  Route::get('settings', 'ModalController@getModalSettings');
});

// Secured routes
Route::prefix('popups')->group(function() {
  Route::get('/', 'LeadPopupsController@getPopupList')->middleware('role_or_permission:' . config('leadpopups.role_or_permission'))->name('LeadPopups');
  Route::get('create', 'LeadPopupsController@getCreatePopup')->middleware('role_or_permission:' . config('leadpopups.role_or_permission'))->name('LeadPopups');
  Route::post('create', 'LeadPopupsController@postCreatePopup')->middleware('role_or_permission:' . config('leadpopups.role_or_permission'))->name('LeadPopups');
  Route::get('edit/{sl}', 'LeadPopupsController@getEditPopup')->middleware('role_or_permission:' . config('leadpopups.role_or_permission'))->name('LeadPopups');
  Route::post('edit/{sl}', 'LeadPopupsController@postEditPopup')->middleware('role_or_permission:' . config('leadpopups.role_or_permission'))->name('LeadPopups');
  Route::get('json', 'LeadPopupsController@getPopupListJson')->middleware('role_or_permission:' . config('leadpopups.role_or_permission'));
  Route::post('delete-popups', 'LeadPopupsController@postDeletePopups')->middleware('role_or_permission:' . config('leadpopups.role_or_permission'));
});
