<?php
namespace App\Modules\Catalog\Presentation\Http\Controllers;
use App\Http\Controllers\Controller;use App\Modules\Catalog\Application\DTOs\CategoryData;use App\Modules\Catalog\Application\UseCases\Categories\CreateCategory;use App\Modules\Catalog\Application\UseCases\Categories\UpdateCategory;use App\Modules\Catalog\Application\UseCases\Categories\DeleteCategory;use App\Modules\Catalog\Domain\Contracts\CategoryRepositoryInterface;use App\Modules\Catalog\Presentation\Http\Requests\StoreCategoryRequest;use App\Modules\Catalog\Presentation\Http\Requests\UpdateCategoryRequest;use Illuminate\Http\JsonResponse;use Illuminate\Http\Request;
class CategoryController extends Controller
{
 public function __construct(private readonly CategoryRepositoryInterface $items){}
 public function index(Request $r):JsonResponse{$this->allow($r,'categories.view');return response()->json(['data'=>$this->items->all()]);}
 public function store(StoreCategoryRequest $r,CreateCategory $u):JsonResponse{return response()->json(['data'=>$u->execute(CategoryData::fromArray($r->validated()))],201);}
 public function show(Request $r,int $id):JsonResponse{$this->allow($r,'categories.view');return response()->json(['data'=>$this->items->findOrFail($id)]);}
 public function update(UpdateCategoryRequest $r,int $id,UpdateCategory $u):JsonResponse{return response()->json(['data'=>$u->execute($id,CategoryData::fromArray($r->validated()))]);}
 public function destroy(Request $r,int $id,DeleteCategory $u):JsonResponse{$this->allow($r,'categories.delete');$u->execute($id);return response()->json(null,204);}
 private function allow(Request $r,string $p):void{abort_unless($r->user()?->hasPermission($p),403);}
}
