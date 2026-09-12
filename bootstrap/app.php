<?php
use App\Modules\Catalog\Domain\Exceptions\AttributeNotFoundException;
use App\Modules\Catalog\Domain\Exceptions\AttributeValueNotFoundException;
use App\Modules\Catalog\Domain\Exceptions\BrandNotFoundException;
use App\Modules\Catalog\Domain\Exceptions\BusinessRuleException;
use App\Modules\Catalog\Domain\Exceptions\CategoryNotFoundException;
use App\Modules\Catalog\Domain\Exceptions\ProductNotFoundException;
use App\Modules\Catalog\Domain\Exceptions\VariantNotFoundException;
use App\Modules\Auth\Domain\Exceptions\AuthenticationException as DomainAuthenticationException;
use App\Modules\Auth\Domain\Exceptions\AuthorizationException as DomainAuthorizationException;
use App\Modules\Customer\Domain\Exceptions\CustomerNotFoundException;
use App\Modules\Customer\Domain\Exceptions\CustomerFeatureNotFoundException;
use App\Modules\Customer\Domain\Exceptions\AddressNotFoundException;
use App\Modules\Settings\Domain\Exceptions\SettingsNotFoundException;
use App\Modules\Staff\Domain\Exceptions\StaffNotFoundException;
use App\Modules\Staff\Domain\Exceptions\StaffActionNotAllowedException;
use App\Modules\Inventory\Domain\Exceptions\InventoryNotFoundException;
use App\Modules\Inventory\Domain\Exceptions\InsufficientStockException;
use App\Modules\Inventory\Domain\Exceptions\InvalidStockAdjustmentException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
return Application::configure(basePath: dirname(__DIR__))
 ->withRouting(web:__DIR__.'/../routes/web.php',api:__DIR__.'/../routes/api.php',commands:__DIR__.'/../routes/console.php',health:'/up')
 ->withMiddleware(function(Middleware $middleware):void{})
 ->withExceptions(function(Exceptions $exceptions):void{
  $exceptions->shouldRenderJsonWhen(fn(Request $request)=>$request->is('api/*')||$request->expectsJson());
  $exceptions->render(function(AuthenticationException $e,Request $r){if($r->is('api/*'))return response()->json(['message'=>'Unauthenticated.'],401);});
  $exceptions->render(function(DomainAuthenticationException $e,Request $r){if($r->is('api/*'))return response()->json(['message'=>$e->getMessage()],401);});
  $exceptions->render(function(DomainAuthorizationException|StaffActionNotAllowedException $e,Request $r){if($r->is('api/*'))return response()->json(['message'=>$e->getMessage()],403);});
  $exceptions->render(function(BusinessRuleException $e,Request $r){if($r->is('api/*'))return response()->json(['message'=>$e->getMessage()],409);});
  $exceptions->render(function(InsufficientStockException|InvalidStockAdjustmentException $e,Request $r){if($r->is('api/*'))return response()->json(['message'=>$e->getMessage()],422);});
  $exceptions->render(function(ProductNotFoundException|VariantNotFoundException|AttributeNotFoundException|AttributeValueNotFoundException|BrandNotFoundException|CategoryNotFoundException|SettingsNotFoundException|CustomerNotFoundException|CustomerFeatureNotFoundException|AddressNotFoundException|StaffNotFoundException|InventoryNotFoundException $e,Request $r){if($r->is('api/*'))return response()->json(['message'=>$e->getMessage()],404);});
  $exceptions->render(function(HttpExceptionInterface $e,Request $r){if($r->is('api/*')&&$e->getStatusCode()===403)return response()->json(['message'=>'Forbidden.'],403);});
 })->create();
