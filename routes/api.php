<?php
use App\Modules\Catalog\Presentation\Http\Controllers\AttributeController;
use App\Modules\Catalog\Presentation\Http\Controllers\BrandController;
use App\Modules\Catalog\Presentation\Http\Controllers\CategoryController;
use App\Modules\Catalog\Presentation\Http\Controllers\ProductController;
use App\Modules\Settings\Presentation\Http\Controllers\SettingsController;
use Illuminate\Support\Facades\Route;
Route::middleware('auth')->group(function () {
 Route::get('products',[ProductController::class,'index']);Route::post('products',[ProductController::class,'store']);Route::get('products/{product}',[ProductController::class,'show']);Route::match(['put','patch'],'products/{product}',[ProductController::class,'update']);Route::delete('products/{product}',[ProductController::class,'destroy']);
 Route::get('products/{product}/variants',[ProductController::class,'variants']);Route::post('products/{product}/variants',[ProductController::class,'storeVariant']);Route::get('products/{product}/variants/{variant}',[ProductController::class,'showVariant']);Route::match(['put','patch'],'products/{product}/variants/{variant}',[ProductController::class,'updateVariant']);Route::delete('products/{product}/variants/{variant}',[ProductController::class,'destroyVariant']);
 Route::get('attributes',[AttributeController::class,'index']);Route::post('attributes',[AttributeController::class,'store']);Route::get('attributes/{attribute}',[AttributeController::class,'show']);Route::match(['put','patch'],'attributes/{attribute}',[AttributeController::class,'update']);Route::delete('attributes/{attribute}',[AttributeController::class,'destroy']);
 Route::get('attributes/{attribute}/values',[AttributeController::class,'values']);Route::post('attributes/{attribute}/values',[AttributeController::class,'storeValue']);Route::get('attributes/{attribute}/values/{value}',[AttributeController::class,'showValue']);Route::match(['put','patch'],'attributes/{attribute}/values/{value}',[AttributeController::class,'updateValue']);Route::delete('attributes/{attribute}/values/{value}',[AttributeController::class,'destroyValue']);
 Route::apiResource('brands',BrandController::class)->parameters(['brands'=>'id']);Route::apiResource('categories',CategoryController::class)->parameters(['categories'=>'id']);
 Route::get('settings',[SettingsController::class,'index']);Route::get('settings/groups/{group}',[SettingsController::class,'group']);Route::get('settings/{key}',[SettingsController::class,'show']);Route::put('settings/{key?}',[SettingsController::class,'update']);
});
