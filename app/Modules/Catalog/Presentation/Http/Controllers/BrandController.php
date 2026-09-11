<?php
namespace App\Modules\Catalog\Presentation\Http\Controllers;
use App\Http\Controllers\Controller;use App\Modules\Catalog\Application\DTOs\BrandData;use App\Modules\Catalog\Application\UseCases\Brands\CreateBrand;use App\Modules\Catalog\Application\UseCases\Brands\UpdateBrand;use App\Modules\Catalog\Application\UseCases\Brands\DeleteBrand;use App\Modules\Catalog\Domain\Contracts\BrandRepositoryInterface;use App\Modules\Catalog\Presentation\Http\Requests\StoreBrandRequest;use App\Modules\Catalog\Presentation\Http\Requests\UpdateBrandRequest;use Illuminate\Http\JsonResponse;use Illuminate\Http\Request;
class BrandController extends Controller
{
 public function __construct(private readonly BrandRepositoryInterface $items){}
 public function index(Request $r):JsonResponse{$this->allow($r,'brands.view');return response()->json(['data'=>$this->items->all()]);}
 public function store(StoreBrandRequest $r,CreateBrand $u):JsonResponse{return response()->json(['data'=>$u->execute(BrandData::fromArray($r->validated()))],201);}
 public function show(Request $r,int $id):JsonResponse{$this->allow($r,'brands.view');return response()->json(['data'=>$this->items->findOrFail($id)]);}
 public function update(UpdateBrandRequest $r,int $id,UpdateBrand $u):JsonResponse{return response()->json(['data'=>$u->execute($id,BrandData::fromArray($r->validated()))]);}
 public function destroy(Request $r,int $id,DeleteBrand $u):JsonResponse{$this->allow($r,'brands.delete');$u->execute($id);return response()->json(null,204);}
 private function allow(Request $r,string $p):void{abort_unless($r->user()?->hasPermission($p),403);}
}
