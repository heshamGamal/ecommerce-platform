<?php

use App\Modules\Catalog\Presentation\Http\Controllers\AttributeController;
use App\Modules\Catalog\Presentation\Http\Controllers\BrandController;
use App\Modules\Catalog\Presentation\Http\Controllers\CategoryController;
use App\Modules\Catalog\Presentation\Http\Controllers\ProductController;
use App\Modules\Catalog\Presentation\Http\Controllers\ProductMediaController;
use Illuminate\Support\Facades\Route;

// Storefront catalog is public; mutation and moderation routes remain protected below.
Route::get('products', [ProductController::class, 'index'])->name('products.index');
Route::get('products/{product}', [ProductController::class, 'show'])->name('products.show');
Route::get('products/{product}/variants', [ProductController::class, 'variants'])->name('products.variants.index');
Route::get('products/{product}/variants/{variant}', [ProductController::class, 'showVariant'])->name('products.variants.show');
Route::middleware('auth')->group(function (): void {
    Route::post('products', [ProductController::class, 'store'])->name('products.store');
    Route::match(['put', 'patch'], 'products/{product}', [ProductController::class, 'update'])->name('products.update');
    Route::delete('products/{product}', [ProductController::class, 'destroy'])->name('products.destroy');
    Route::post('products/{product}/variants', [ProductController::class, 'storeVariant'])->name('products.variants.store');
    Route::match(['put', 'patch'], 'products/{product}/variants/{variant}', [ProductController::class, 'updateVariant'])->name('products.variants.update');
    Route::delete('products/{product}/variants/{variant}', [ProductController::class, 'destroyVariant'])->name('products.variants.destroy');
    Route::post('products/{product}/media', [ProductMediaController::class, 'store'])->name('products.media.store');
    Route::delete('products/{product}/media/{media}', [ProductMediaController::class, 'destroy'])->name('products.media.destroy');
    Route::get('products/{product}/media', [ProductMediaController::class, 'index'])->name('products.media.index');
    Route::patch('products/{product}/media/{media}/order', [ProductMediaController::class, 'reorder'])->name('products.media.reorder');
    Route::post('products/{product}/variants/{variant}/media', [ProductMediaController::class, 'variantStore'])->name('products.variants.media.store');
    Route::delete('products/{product}/variants/{variant}/media/{media}', [ProductMediaController::class, 'variantDestroy'])->name('products.variants.media.destroy');
    Route::patch('products/{product}/variants/{variant}/media/{media}/order', [ProductMediaController::class, 'variantReorder'])->name('products.variants.media.reorder');
    Route::get('products/{product}/variants/{variant}/media', [ProductMediaController::class, 'variantIndex'])->name('products.variants.media.index');
    Route::get('attributes', [AttributeController::class, 'index'])->name('attributes.index');
    Route::get('attributes/{attribute}', [AttributeController::class, 'show'])->name('attributes.show');
    Route::get('attributes/{attribute}/values', [AttributeController::class, 'values'])->name('attributes.values.index');
    Route::get('attributes/{attribute}/values/{value}', [AttributeController::class, 'showValue'])->name('attributes.values.show');
    Route::post('attributes', [AttributeController::class, 'store'])->name('attributes.store');
    Route::match(['put', 'patch'], 'attributes/{attribute}', [AttributeController::class, 'update'])->name('attributes.update');
    Route::delete('attributes/{attribute}', [AttributeController::class, 'destroy'])->name('attributes.destroy');
    Route::post('attributes/{attribute}/values', [AttributeController::class, 'storeValue'])->name('attributes.values.store');
    Route::match(['put', 'patch'], 'attributes/{attribute}/values/{value}', [AttributeController::class, 'updateValue'])->name('attributes.values.update');
    Route::delete('attributes/{attribute}/values/{value}', [AttributeController::class, 'destroyValue'])->name('attributes.values.destroy');
    Route::apiResource('brands', BrandController::class)->parameters(['brands' => 'id']);
    Route::apiResource('categories', CategoryController::class)->parameters(['categories' => 'id']);
});
