<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

// Regular page routes
Route::get('/', 'PagesController@index')->name('index');
Route::get('/about', 'PagesController@about')->name('about');
Route::get('/contact', 'PagesController@contact')->name('contact');
Route::get('/terms', 'PagesController@terms')->name('terms');
Route::get('/privacy', 'PagesController@privacy')->name('privacy');
Route::get('/license', 'PagesController@license')->name('license');
Route::get('/language/{locale?}', 'PagesController@language')->name('language');

// Dashboard route
Route::get('/dashboard', 'DashboardController@index')->name('dashboard');

// Authentication routes
Route::get('/login', 'LoginController@login')->name('login');
Route::post('/login', 'LoginController@doLogin');
Route::get('/register', 'RegisterController@register')->name('register');
Route::post('/register', 'RegisterController@doRegister');
Route::get('/logout', 'LogoutController@logout')->name('logout');
Route::prefix('/password')->group(function() {
    Route::get('/change', 'PasswordChangeController@change')->name('password.change');
    Route::post('/change', 'PasswordChangeController@doChange')->name('password.change');
    Route::get('/request', 'PasswordForgetController@request')->name('password.request');
    Route::post('/request', 'PasswordForgetController@doRequest');
    Route::get('/reset/{token?}', 'PasswordResetController@reset')->name('password.reset');
    Route::post('/reset', 'PasswordResetController@doReset');
});

// Email routes
Route::prefix('/email/verify')->group(function() {
    Route::get('/{token?}', 'EmailVerifyController@verify')->name('email.verify');
    Route::post('/', 'EmailVerifyController@doVerify');
});

// Account routes
Route::prefix('/account')->group(function() {
    Route::get('/{userId?}', 'AccountController@show')->name('account');
    Route::get('/{userId?}/emails', 'EmailController@show')->name('account.emails');
});

// Profile routes
Route::prefix('/profile')->group(function() {
    Route::get('/{userId}/edit', 'ProfileController@edit')->name('profile.edit');
    Route::put('/{userId}', 'ProfileController@update')->name('profile.update');
});

// Permission group routes
Route::prefix('/permissions/groups')->group(function() {
    Route::get('/create', 'PermissionGroupsController@create')->name('permissionGroups.create');
    Route::post('/', 'PermissionGroupsController@store')->name('permissionGroups.store');
    Route::get('/', 'PermissionGroupsController@index')->name('permissionGroups.index');
    Route::get('/{id}', 'PermissionGroupsController@show')->name('permissionGroups.show');
    Route::get('/{id}/edit', 'PermissionGroupsController@edit')->name('permissionGroups.edit');
    Route::put('/{id}', 'PermissionGroupsController@update')->name('permissionGroups.update');
    Route::delete('/{id}', 'PermissionGroupsController@destroy')->name('permissionGroups.delete');
});

// TODO: Routes to implement
Route::get('/email/preferences', 'DashboardController@index')->name('email.preferences');

// Posts
Route::resource('posts', 'PostsController');
