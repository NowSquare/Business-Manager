<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Public routes
Route::group(['prefix' => 'newsletters'], function () {
  Route::get('unsubscribe', 'NewslettersController@getUnsubscribeNewsletter');
});

// Secured routes
Route::prefix('newsletters')->group(function() {
  Route::get('/', 'NewslettersController@getNewsletterList')->middleware('role_or_permission:' . config('newsletters.role_or_permission'))->name('Newsletters');
  Route::get('create/{template?}', 'NewslettersController@getCreateNewsletter')->middleware('role_or_permission:' . config('newsletters.role_or_permission'))->name('Newsletters');
  Route::post('create', 'NewslettersController@postCreateNewsletter')->middleware('role_or_permission:' . config('newsletters.role_or_permission'));
  Route::post('duplicate', 'NewslettersController@postDuplicateNewsletter')->middleware('role_or_permission:' . config('newsletters.role_or_permission'));
  Route::get('edit/{sl}', 'NewslettersController@getEditNewsletter')->middleware('role_or_permission:' . config('newsletters.role_or_permission'))->name('Newsletters');
  Route::post('edit/{sl}', 'NewslettersController@postEditNewsletter')->middleware('role_or_permission:' . config('newsletters.role_or_permission'));
  Route::get('json', 'NewslettersController@getNewsletterListJson')->middleware('role_or_permission:' . config('newsletters.role_or_permission'));
  Route::post('delete-newsletters', 'NewslettersController@postDeleteNewsletters')->middleware('role_or_permission:' . config('newsletters.role_or_permission'));

  // Send newsletter
  Route::post('test', 'NewslettersController@postSendTestNewsletter')->middleware('role_or_permission:' . config('newsletters.role_or_permission'));
  Route::post('send', 'NewslettersController@postSendNewsletter')->middleware('role_or_permission:' . config('newsletters.role_or_permission'));
  Route::get('send', 'NewslettersController@postSendNewsletter')->middleware('role_or_permission:' . config('newsletters.role_or_permission'));

  // Editor
  Route::post('editor/assets', 'NewslettersController@postGetEditorAssets')->middleware('role_or_permission:' . config('newsletters.role_or_permission'));
  Route::post('editor/assets/upload', 'NewslettersController@postUploadEditorAssets')->middleware('role_or_permission:' . config('newsletters.role_or_permission'));
});
