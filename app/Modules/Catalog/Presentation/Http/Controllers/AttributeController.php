<?php
namespace App\Modules\Catalog\Presentation\Http\Controllers;
use App\Http\Controllers\Controller;use App\Modules\Catalog\Application\DTOs\AttributeData;use App\Modules\Catalog\Application\DTOs\AttributeValueData;use App\Modules\Catalog\Application\UseCases\Attributes\CreateAttribute;use App\Modules\Catalog\Application\UseCases\Attributes\CreateAttributeValue;use App\Modules\Catalog\Application\UseCases\Attributes\DeleteAttribute;use App\Modules\Catalog\Application\UseCases\Attributes\DeleteAttributeValue;use App\Modules\Catalog\Application\UseCases\Attributes\UpdateAttribute;use App\Modules\Catalog\Application\UseCases\Attributes\UpdateAttributeValue;use App\Modules\Catalog\Domain\Contracts\AttributeRepositoryInterface;use App\Modules\Catalog\Domain\Contracts\AttributeValueRepositoryInterface;use App\Modules\Catalog\Presentation\Http\Requests\StoreAttributeRequest;use App\Modules\Catalog\Presentation\Http\Requests\StoreAttributeValueRequest;use App\Modules\Catalog\Presentation\Http\Requests\UpdateAttributeRequest;use App\Modules\Catalog\Presentation\Http\Requests\UpdateAttributeValueRequest;use Illuminate\Http\JsonResponse;use Illuminate\Http\Request;
class AttributeController extends Controller
{
 public function __construct(private readonly AttributeRepositoryInterface $attributes,private readonly AttributeValueRepositoryInterface $values){}
 public function index(Request $r):JsonResponse{$this->allow($r,'products.view');return response()->json(['data'=>$this->attributes->all()]);}
 public function store(StoreAttributeRequest $r,CreateAttribute $u):JsonResponse{return response()->json(['data'=>$u->execute(AttributeData::fromArray($r->validated()))],201);}
 public function show(Request $r,int $attribute):JsonResponse{$this->allow($r,'products.view');return response()->json(['data'=>$this->attributes->findOrFail($attribute)]);}
 public function update(UpdateAttributeRequest $r,int $attribute,UpdateAttribute $u):JsonResponse{return response()->json(['data'=>$u->execute($attribute,AttributeData::fromArray($r->validated()))]);}
 public function destroy(Request $r,int $attribute,DeleteAttribute $u):JsonResponse{$this->allow($r,'products.delete');$u->execute($attribute);return response()->json(null,204);}
 public function values(Request $r,int $attribute):JsonResponse{$this->allow($r,'products.view');$this->attributes->findOrFail($attribute);return response()->json(['data'=>$this->values->forAttribute($attribute)]);}
 public function storeValue(StoreAttributeValueRequest $r,int $attribute,CreateAttributeValue $u):JsonResponse{$d=$r->validated();$d['attribute_id']=$attribute;return response()->json(['data'=>$u->execute(AttributeValueData::fromArray($d))],201);}
 public function showValue(Request $r,int $attribute,int $value):JsonResponse{$this->allow($r,'products.view');return response()->json(['data'=>$this->values->findOrFail($value,$attribute)]);}
 public function updateValue(UpdateAttributeValueRequest $r,int $attribute,int $value,UpdateAttributeValue $u):JsonResponse{$d=$r->validated();$d['attribute_id']=$attribute;return response()->json(['data'=>$u->execute($attribute,$value,AttributeValueData::fromArray($d))]);}
 public function destroyValue(Request $r,int $attribute,int $value,DeleteAttributeValue $u):JsonResponse{$this->allow($r,'products.delete');$u->execute($attribute,$value);return response()->json(null,204);}
 private function allow(Request $r,string $p):void{abort_unless($r->user()?->hasPermission($p),403);}
}
