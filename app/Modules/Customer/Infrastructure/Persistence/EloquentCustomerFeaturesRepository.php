<?php
namespace App\Modules\Customer\Infrastructure\Persistence;
use App\Models\CustomerAddress; use App\Models\CustomerCart; use App\Models\CustomerNotification; use App\Models\CustomerOrder; use App\Models\CustomerPreference; use App\Models\CustomerWishlist; use App\Modules\Customer\Domain\Contracts\CustomerFeaturesRepositoryInterface;
final class EloquentCustomerFeaturesRepository implements CustomerFeaturesRepositoryInterface {
 public function addresses(int $userId): iterable{return CustomerAddress::query()->where('user_id',$userId)->latest()->get();}
 public function defaultAddress(int $userId):?CustomerAddress{return CustomerAddress::query()->where('user_id',$userId)->where('is_default',true)->first();}
 public function address(int $userId,int $id):?CustomerAddress{return CustomerAddress::query()->where('user_id',$userId)->find($id);}
 public function createAddress(int $userId,array $data):CustomerAddress{$data['user_id']=$userId;return CustomerAddress::query()->create($data);}
 public function updateAddress(CustomerAddress $address,array $data):CustomerAddress{$address->update($data);return $address->fresh();}
 public function deleteAddress(CustomerAddress $address):void{$address->delete();}
 public function clearDefaultAddress(int $userId):void{CustomerAddress::query()->where('user_id',$userId)->where('is_default',true)->update(['is_default'=>false]);}
 public function orders(int $userId):iterable{return CustomerOrder::query()->with('items.product')->where('user_id',$userId)->latest()->get();}
 public function cart(int $userId):CustomerCart{return CustomerCart::query()->firstOrCreate(['user_id'=>$userId]);}
 public function addCartItem(CustomerCart $cart,int $productId,int $quantity):CustomerCart{$item=$cart->items()->firstOrNew(['product_id'=>$productId]);$item->quantity=($item->exists?$item->quantity:0)+$quantity;$item->save();return $cart->fresh('items.product');}
 public function removeCartItem(CustomerCart $cart,int $productId):CustomerCart{$cart->items()->where('product_id',$productId)->delete();return $cart->fresh('items.product');}
 public function updateCartItem(CustomerCart $cart,int $productId,int $quantity):CustomerCart{$cart->items()->where('product_id',$productId)->update(['quantity'=>$quantity]);return $cart->fresh('items.product');}
 public function wishlist(int $userId):iterable{return CustomerWishlist::query()->with('product')->where('user_id',$userId)->latest()->get();}
 public function addWishlistItem(int $userId,int $productId):CustomerWishlist{return CustomerWishlist::query()->firstOrCreate(['user_id'=>$userId,'product_id'=>$productId]);}
 public function removeWishlistItem(int $userId,int $productId):void{CustomerWishlist::query()->where('user_id',$userId)->where('product_id',$productId)->delete();}
 public function preferences(int $userId):CustomerPreference{return CustomerPreference::query()->firstOrCreate(['user_id'=>$userId],['data'=>[]]);}
 public function updatePreferences(int $userId,array $data):CustomerPreference{$p=$this->preferences($userId);$p->update(['data'=>$data]);return $p->fresh();}
 public function notifications(int $userId):iterable{return CustomerNotification::query()->where('user_id',$userId)->latest()->get();}
 public function markNotificationRead(int $userId,int $id):CustomerNotification{$n=CustomerNotification::query()->where('user_id',$userId)->findOrFail($id);$n->update(['read_at'=>now()]);return $n->fresh();}
}
