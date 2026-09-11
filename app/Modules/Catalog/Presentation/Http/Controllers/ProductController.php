<?php
namespace App\Modules\Catalog\Presentation\Http\Controllers;
use App\Http\Controllers\Controller;
use App\Modules\Catalog\Application\DTOs\ProductData;
use App\Modules\Catalog\Application\DTOs\ProductVariantData;
use App\Modules\Catalog\Application\UseCases\Products\CreateProduct;
use App\Modules\Catalog\Application\UseCases\Products\CreateProductVariant;
use App\Modules\Catalog\Application\UseCases\Products\DeleteProduct;
use App\Modules\Catalog\Application\UseCases\Products\DeleteProductVariant;
use App\Modules\Catalog\Application\UseCases\Products\UpdateProduct;
use App\Modules\Catalog\Application\UseCases\Products\UpdateProductVariant;
use App\Modules\Catalog\Domain\Contracts\ProductRepositoryInterface;
use App\Modules\Catalog\Presentation\Http\Requests\StoreProductRequest;
use App\Modules\Catalog\Presentation\Http\Requests\StoreProductVariantRequest;
use App\Modules\Catalog\Presentation\Http\Requests\UpdateProductRequest;
use App\Modules\Catalog\Presentation\Http\Requests\UpdateProductVariantRequest;
use Illuminate\Http\JsonResponse;use Illuminate\Http\Request;
class ProductController extends Controller
{
 public function __construct(private readonly ProductRepositoryInterface $products){}
 public function index(Request $r):JsonResponse{$this->allow($r,'products.view');return response()->json(['data'=>$this->products->all()]);}
 public function store(StoreProductRequest $r,CreateProduct $u):JsonResponse{return response()->json(['data'=>$u->execute(ProductData::fromArray($r->validated()))],201);}
 public function show(Request $r,int $product):JsonResponse{$this->allow($r,'products.view');return response()->json(['data'=>$this->products->findOrFail($product)]);}
 public function update(UpdateProductRequest $r,int $product,UpdateProduct $u):JsonResponse{return response()->json(['data'=>$u->execute($product,ProductData::fromArray($r->validated()))]);}
 public function destroy(Request $r,int $product,DeleteProduct $u):JsonResponse{$this->allow($r,'products.delete');$u->execute($product);return response()->json(null,204);}
 public function variants(Request $r,int $product):JsonResponse{$this->allow($r,'products.view');$p=$this->products->findOrFail($product);return response()->json(['data'=>$this->products->variants($p)]);}
 public function storeVariant(StoreProductVariantRequest $r,int $product,CreateProductVariant $u):JsonResponse{return response()->json(['data'=>$u->execute($product,ProductVariantData::fromArray($r->validated()))],201);}
 public function showVariant(Request $r,int $product,int $variant):JsonResponse{$this->allow($r,'products.view');$p=$this->products->findOrFail($product);return response()->json(['data'=>$this->products->findVariantOrFail($p,$variant)]);}
 public function updateVariant(UpdateProductVariantRequest $r,int $product,int $variant,UpdateProductVariant $u):JsonResponse{return response()->json(['data'=>$u->execute($product,$variant,ProductVariantData::fromArray($r->validated()))]);}
 public function destroyVariant(Request $r,int $product,int $variant,DeleteProductVariant $u):JsonResponse{$this->allow($r,'products.delete');$u->execute($product,$variant);return response()->json(null,204);}
 private function allow(Request $r,string $permission):void{abort_unless($r->user()?->hasPermission($permission),403);}
}
