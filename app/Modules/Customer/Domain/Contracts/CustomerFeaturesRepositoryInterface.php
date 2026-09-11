<?php
namespace App\Modules\Customer\Domain\Contracts;
use App\Models\CustomerAddress;
use App\Models\CustomerCart;
use App\Models\CustomerNotification;
use App\Models\CustomerOrder;
use App\Models\CustomerPreference;
use App\Models\CustomerWishlist;
interface CustomerFeaturesRepositoryInterface {
 public function addresses(int $userId): iterable; public function defaultAddress(int $userId): ?CustomerAddress; public function address(int $userId,int $id): ?CustomerAddress; public function createAddress(int $userId,array $data): CustomerAddress; public function updateAddress(CustomerAddress $address,array $data): CustomerAddress; public function deleteAddress(CustomerAddress $address): void; public function clearDefaultAddress(int $userId): void;
 public function orders(int $userId): iterable;
 public function cart(int $userId): CustomerCart; public function addCartItem(CustomerCart $cart,int $productId,int $quantity): CustomerCart; public function removeCartItem(CustomerCart $cart,int $productId): CustomerCart; public function updateCartItem(CustomerCart $cart,int $productId,int $quantity): CustomerCart;
 public function wishlist(int $userId): iterable; public function addWishlistItem(int $userId,int $productId): CustomerWishlist; public function removeWishlistItem(int $userId,int $productId): void;
 public function preferences(int $userId): CustomerPreference; public function updatePreferences(int $userId,array $data): CustomerPreference;
 public function notifications(int $userId): iterable; public function markNotificationRead(int $userId,int $id): CustomerNotification;
}
