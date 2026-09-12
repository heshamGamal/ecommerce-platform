<?php
use App\Modules\Auth\Presentation\Http\Controllers\AuthController;
use App\Modules\Catalog\Presentation\Http\Controllers\AttributeController;
use App\Modules\Catalog\Presentation\Http\Controllers\BrandController;
use App\Modules\Catalog\Presentation\Http\Controllers\CategoryController;
use App\Modules\Catalog\Presentation\Http\Controllers\ProductController;
use App\Modules\Customer\Presentation\Http\Controllers\CustomerController;
use App\Modules\Customer\Presentation\Http\Controllers\CustomerFeaturesController;
use App\Modules\Settings\Presentation\Http\Controllers\SettingsController;
use App\Modules\Staff\Presentation\Http\Controllers\StaffController;
use App\Modules\Inventory\Presentation\Http\Controllers\InventoryController;
use Illuminate\Support\Facades\Route;

Route::post('auth/register', [AuthController::class, 'register'])->middleware('guest')->name('auth.register');
Route::post('auth/login', [AuthController::class, 'login'])->middleware(['guest', 'throttle:auth-login'])->name('auth.login');

Route::middleware('auth')->group(function () {
    Route::get('auth/me', [AuthController::class, 'me'])->name('auth.me');
    Route::post('auth/logout', [AuthController::class, 'logout'])->name('auth.logout');
    Route::post('auth/password', [AuthController::class, 'changePassword'])->name('auth.password');
    Route::get('staff', [StaffController::class, 'index'])->name('staff.index');
    Route::post('staff', [StaffController::class, 'store'])->name('staff.store');
    Route::match(['put', 'patch'], 'staff/{id}', [StaffController::class, 'update'])->name('staff.update');
    Route::delete('staff/{id}', [StaffController::class, 'destroy'])->name('staff.destroy');
    Route::get('inventory', [InventoryController::class, 'index'])->name('inventory.index');
    Route::post('inventory/adjust', [InventoryController::class, 'adjust'])->name('inventory.adjust');
    Route::post('inventory/reserve', [InventoryController::class, 'reserve'])->name('inventory.reserve');
    Route::post('inventory/release', [InventoryController::class, 'release'])->name('inventory.release');
    Route::get('customer/profile', [CustomerController::class, 'profile'])->name('customer.profile');
    Route::match(['put', 'patch'], 'customer/profile', [CustomerController::class, 'updateProfile'])->name('customer.profile.update');
    Route::get('customer/addresses', [CustomerFeaturesController::class, 'addresses'])->name('customer.addresses.index');
    Route::get('customer/addresses/default', [CustomerFeaturesController::class, 'defaultAddress'])->name('customer.addresses.default');
    Route::post('customer/addresses', [CustomerFeaturesController::class, 'addAddress'])->name('customer.addresses.store');
    Route::match(['put', 'patch'], 'customer/addresses/{id}', [CustomerFeaturesController::class, 'updateAddress'])->name('customer.addresses.update');
    Route::delete('customer/addresses/{id}', [CustomerFeaturesController::class, 'deleteAddress'])->name('customer.addresses.destroy');
    Route::get('customer/orders', [CustomerFeaturesController::class, 'orders'])->name('customer.orders.index');
    Route::get('customer/cart', [CustomerFeaturesController::class, 'cart'])->name('customer.cart.show');
    Route::post('customer/cart/items', [CustomerFeaturesController::class, 'addCartItem'])->name('customer.cart.items.store');
    Route::patch('customer/cart/items', [CustomerFeaturesController::class, 'updateCartItem'])->name('customer.cart.items.update');
    Route::delete('customer/cart/items/{productId}', [CustomerFeaturesController::class, 'removeCartItem'])->name('customer.cart.items.destroy');
    Route::get('customer/wishlist', [CustomerFeaturesController::class, 'wishlist'])->name('customer.wishlist.index');
    Route::post('customer/wishlist', [CustomerFeaturesController::class, 'addWishlist'])->name('customer.wishlist.store');
    Route::delete('customer/wishlist/{productId}', [CustomerFeaturesController::class, 'removeWishlist'])->name('customer.wishlist.destroy');
    Route::get('customer/preferences', [CustomerFeaturesController::class, 'preferences'])->name('customer.preferences.show');
    Route::put('customer/preferences', [CustomerFeaturesController::class, 'updatePreferences'])->name('customer.preferences.update');
    Route::get('customer/notifications', [CustomerFeaturesController::class, 'notifications'])->name('customer.notifications.index');
    Route::patch('customer/notifications/{id}/read', [CustomerFeaturesController::class, 'readNotification'])->name('customer.notifications.read');
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
