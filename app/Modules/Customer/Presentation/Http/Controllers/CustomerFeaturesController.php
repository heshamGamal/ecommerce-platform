<?php
namespace App\Modules\Customer\Presentation\Http\Controllers;
use App\Http\Controllers\Controller;use App\Modules\Customer\Application\DTOs\AddressData;use App\Modules\Customer\Application\UseCases\CreateAddress;use App\Modules\Customer\Application\UseCases\DeleteAddress;use App\Modules\Customer\Application\UseCases\GetDefaultAddress;use App\Modules\Customer\Application\UseCases\GetCustomerOrders;use App\Modules\Customer\Application\UseCases\ListAddresses;use App\Modules\Customer\Application\UseCases\ManageCustomerCart;use App\Modules\Customer\Application\UseCases\ManageCustomerNotifications;use App\Modules\Customer\Application\UseCases\ManageCustomerPreferences;use App\Modules\Customer\Application\UseCases\ManageCustomerWishlist;use App\Modules\Customer\Application\UseCases\UpdateAddress;use App\Modules\Customer\Presentation\Http\Requests\AddressRequest;use App\Modules\Customer\Presentation\Http\Requests\CartAccessRequest;use App\Modules\Customer\Presentation\Http\Requests\CartItemRequest;use App\Modules\Customer\Presentation\Http\Requests\NotificationRequest;use App\Modules\Customer\Presentation\Http\Requests\OrdersRequest;use App\Modules\Customer\Presentation\Http\Requests\PreferencesRequest;use App\Modules\Customer\Presentation\Http\Requests\ProductRequest;use Illuminate\Http\JsonResponse;
final class CustomerFeaturesController extends Controller{
 public function addresses(AddressRequest $r,ListAddresses $u):JsonResponse{return response()->json(['data'=>$u->execute()]);}
 public function defaultAddress(AddressRequest $r,GetDefaultAddress $u):JsonResponse{return response()->json(['data'=>$u->execute()]);}
 public function addAddress(AddressRequest $r,CreateAddress $u):JsonResponse{return response()->json(['data'=>$u->execute(AddressData::fromArray($r->validated()))],201);}
 public function updateAddress(AddressRequest $r,int $id,UpdateAddress $u):JsonResponse{return response()->json(['data'=>$u->execute($id,AddressData::fromArray($r->validated()))]);}
 public function deleteAddress(AddressRequest $r,int $id,DeleteAddress $u):JsonResponse{$u->execute($id);return response()->json(null,204);}
 public function orders(OrdersRequest $r,GetCustomerOrders $u):JsonResponse{return response()->json(['data'=>$u->execute()]);}
 public function cart(CartAccessRequest $r,ManageCustomerCart $u):JsonResponse{return response()->json(['data'=>$u->show()]);}
 public function addCartItem(CartItemRequest $r,ManageCustomerCart $u):JsonResponse{return response()->json(['data'=>$u->add((int)$r->validated('product_id'),(int)$r->validated('quantity'))],201);}
 public function updateCartItem(CartItemRequest $r,ManageCustomerCart $u):JsonResponse{return response()->json(['data'=>$u->update((int)$r->validated('product_id'),(int)$r->validated('quantity'))]);}
 public function removeCartItem(CartItemRequest $r,int $productId,ManageCustomerCart $u):JsonResponse{return response()->json(['data'=>$u->remove($productId)]);}
 public function wishlist(ProductRequest $r,ManageCustomerWishlist $u):JsonResponse{return response()->json(['data'=>$u->list()]);}
 public function addWishlist(ProductRequest $r,ManageCustomerWishlist $u):JsonResponse{return response()->json(['data'=>$u->add((int)$r->validated('product_id'))],201);}
 public function removeWishlist(ProductRequest $r,int $productId,ManageCustomerWishlist $u):JsonResponse{$u->remove($productId);return response()->json(null,204);}
 public function preferences(PreferencesRequest $r,ManageCustomerPreferences $u):JsonResponse{return response()->json(['data'=>$u->show()]);}
 public function updatePreferences(PreferencesRequest $r,ManageCustomerPreferences $u):JsonResponse{return response()->json(['data'=>$u->update($r->validated('data'))]);}
 public function notifications(NotificationRequest $r,ManageCustomerNotifications $u):JsonResponse{return response()->json(['data'=>$u->list()]);}
 public function readNotification(NotificationRequest $r,int $id,ManageCustomerNotifications $u):JsonResponse{return response()->json(['data'=>$u->read($id)]);}
}
