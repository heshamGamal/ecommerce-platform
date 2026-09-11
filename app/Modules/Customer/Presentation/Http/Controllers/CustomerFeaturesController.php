<?php
namespace App\Modules\Customer\Presentation\Http\Controllers;
use App\Http\Controllers\Controller;
 use App\Modules\Customer\Application\UseCases\ListCustomerAddresses;use App\Modules\Customer\Application\UseCases\GetDefaultCustomerAddress;use App\Modules\Customer\Application\UseCases\ManageCustomerAddress;use App\Modules\Customer\Application\UseCases\GetCustomerOrders;use App\Modules\Customer\Application\UseCases\ManageCustomerCart;use App\Modules\Customer\Application\UseCases\ManageCustomerWishlist;use App\Modules\Customer\Application\UseCases\ManageCustomerPreferences;use App\Modules\Customer\Application\UseCases\ManageCustomerNotifications;
use App\Modules\Customer\Presentation\Http\Requests\AddressRequest;use App\Modules\Customer\Presentation\Http\Requests\CartAccessRequest;use App\Modules\Customer\Presentation\Http\Requests\CartItemRequest;use App\Modules\Customer\Presentation\Http\Requests\OrdersRequest;use App\Modules\Customer\Presentation\Http\Requests\ProductRequest;use App\Modules\Customer\Presentation\Http\Requests\PreferencesRequest;use App\Modules\Customer\Presentation\Http\Requests\NotificationRequest;use Illuminate\Http\JsonResponse;
final class CustomerFeaturesController extends Controller {
 public function addresses(AddressRequest $r,ListCustomerAddresses $u):JsonResponse{return response()->json(['data'=>$u->execute()]);}
 public function defaultAddress(AddressRequest $r,GetDefaultCustomerAddress $u):JsonResponse{return response()->json(['data'=>$u->execute()]);}
 public function addAddress(AddressRequest $r,ManageCustomerAddress $u):JsonResponse{return response()->json(['data'=>$u->create($r->validated())],201);}
 public function updateAddress(AddressRequest $r,int $id,ManageCustomerAddress $u):JsonResponse{return response()->json(['data'=>$u->update($id,$r->validated())]);}
 public function deleteAddress(AddressRequest $r,int $id,ManageCustomerAddress $u):JsonResponse{$u->delete($id);return response()->json(null,204);}
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
