<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Public routes, cross-domain accessible
Route::group(['middleware' => 'cors', 'prefix' => 'coupon'], function () {
  Route::get('{slug}', 'CouponController@getCoupon');
  Route::post('redeem/{slug}', 'CouponController@postRedeemCoupon');
  Route::get('redeemed/{slug}', 'CouponController@getCouponRedeemed');
  Route::get('verify/{slug}', 'CouponController@getVerifyCoupon');
  Route::post('verify/{slug}', 'CouponController@postVerifyCoupon');
});

// Secured routes
Route::prefix('coupons')->group(function() {
  Route::get('/', 'CouponController@getCouponList')->middleware('role_or_permission:' . config('coupons.role_or_permission'))->name('Coupons');
  Route::get('create', 'CouponController@getCreateCoupon')->middleware('role_or_permission:' . config('coupons.role_or_permission'))->name('Coupons');
  Route::post('create', 'CouponController@postCreateCoupon')->middleware('role_or_permission:' . config('coupons.role_or_permission'))->name('Coupons');
  Route::get('edit/{sl}', 'CouponController@getEditCoupon')->middleware('role_or_permission:' . config('coupons.role_or_permission'))->name('Coupons');
  Route::post('edit/{sl}', 'CouponController@postEditCoupon')->middleware('role_or_permission:' . config('coupons.role_or_permission'))->name('Coupons');
  Route::get('json', 'CouponController@getCouponListJson')->middleware('role_or_permission:' . config('coupons.role_or_permission'));
  Route::post('delete-coupons', 'CouponController@postDeleteCoupons')->middleware('role_or_permission:' . config('coupons.role_or_permission'));
});