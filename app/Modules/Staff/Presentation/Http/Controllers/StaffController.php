<?php
namespace App\Modules\Staff\Presentation\Http\Controllers;
use App\Http\Controllers\Controller;use App\Modules\Staff\Application\DTOs\StaffData;use App\Modules\Staff\Application\UseCases\CreateStaff;use App\Modules\Staff\Application\UseCases\DeleteStaff;use App\Modules\Staff\Application\UseCases\ListStaff;use App\Modules\Staff\Application\UseCases\UpdateStaff;use App\Modules\Staff\Domain\Contracts\StaffRepositoryInterface;use App\Modules\Staff\Presentation\Http\Requests\StaffRequest;use Illuminate\Http\JsonResponse;
final class StaffController extends Controller
{
 public function index(StaffRequest $request,ListStaff $useCase):JsonResponse{return response()->json(['data'=>$useCase->execute()]);}
 public function store(StaffRequest $request,CreateStaff $useCase):JsonResponse{return response()->json(['data'=>$useCase->execute(StaffData::fromArray($request->validated()))],201);}
 public function update(StaffRequest $request,int $id,StaffRepositoryInterface $repository,UpdateStaff $useCase):JsonResponse{return response()->json(['data'=>$useCase->execute($repository->find($id),StaffData::fromArray($request->validated()),$request->user())]);}
 public function destroy(StaffRequest $request,int $id,StaffRepositoryInterface $repository,DeleteStaff $useCase):JsonResponse{$useCase->execute($repository->find($id),$request->user());return response()->json(['message'=>'Staff deleted successfully.']);}
}
