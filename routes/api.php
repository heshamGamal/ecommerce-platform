<?php
use App\Modules\Catalog\Presentation\Http\Controllers\AttributeController;
use App\Modules\Catalog\Presentation\Http\Controllers\BrandController;
use App\Modules\Catalog\Presentation\Http\Controllers\CategoryController;
use App\Modules\Catalog\Presentation\Http\Controllers\ProductController;
use App\Modules\Settings\Presentation\Http\Controllers\SettingsController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->group(function () {
    Route::get('products', [ProductController::class, 'index'])->name('products.index');
    Route::post('products', [ProductController::class, 'store'])->name('products.store');
    Route::get('products/{product}', [ProductController::class, 'show'])->name('products.show');
    Route::match(['put', 'patch'], 'products/{product}', [ProductController::class, 'update'])->name('products.update');
    Route::delete('products/{product}', [ProductController::class, 'destroy'])->name('products.destroy');

    Route::get('products/{product}/variants', [ProductController::class, 'variants'])->name('products.variants.index');
    Route::post('products/{product}/variants', [ProductController::class, 'storeVariant'])->name('products.variants.store');
    Route::get('products/{product}/variants/{variant}', [ProductController::class, 'showVariant'])->name('products.variants.show');
    Route::match(['put', 'patch'], 'products/{product}/variants/{variant}', [ProductController::class, 'updateVariant'])->name('products.variants.update');
    Route::delete('products/{product}/variants/{variant}', [ProductController::class, 'destroyVariant'])->name('products.variants.destroy');

    Route::get('attributes', [AttributeController::class, 'index'])->name('attributes.index');
    Route::post('attributes', [AttributeController::class, 'store'])->name('attributes.store');
    Route::get('attributes/{attribute}', [AttributeController::class, 'show'])->name('attributes.show');
    Route::match(['put', 'patch'], 'attributes/{attribute}', [AttributeController::class, 'update'])->name('attributes.update');
    Route::delete('attributes/{attribute}', [AttributeController::class, 'destroy'])->name('attributes.destroy');

    Route::get('attributes/{attribute}/values', [AttributeController::class, 'values'])->name('attributes.values.index');
    Route::post('attributes/{attribute}/values', [AttributeController::class, 'storeValue'])->name('attributes.values.store');
    Route::get('attributes/{attribute}/values/{value}', [AttributeController::class, 'showValue'])->name('attributes.values.show');
    Route::match(['put', 'patch'], 'attributes/{attribute}/values/{value}', [AttributeController::class, 'updateValue'])->name('attributes.values.update');
    Route::delete('attributes/{attribute}/values/{value}', [AttributeController::class, 'destroyValue'])->name('attributes.values.destroy');

    Route::apiResource('brands', BrandController::class)->parameters(['brands' => 'id']);
    Route::apiResource('categories', CategoryController::class)->parameters(['categories' => 'id']);

    Route::get('settings', [SettingsController::class, 'index'])->name('settings.index');
    Route::get('settings/groups/{group}', [SettingsController::class, 'group'])->name('settings.group');
    Route::get('settings/{key}', [SettingsController::class, 'show'])->name('settings.show');
    Route::put('settings/{key?}', [SettingsController::class, 'update'])->name('settings.update');
});
