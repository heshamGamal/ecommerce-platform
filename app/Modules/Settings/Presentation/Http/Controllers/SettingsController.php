<?php
namespace App\Modules\Settings\Presentation\Http\Controllers;
use App\Http\Controllers\Controller;use App\Modules\Settings\Application\DTOs\SettingData;use App\Modules\Settings\Application\DTOs\SettingsData;use App\Modules\Settings\Application\UseCases\GetSetting;use App\Modules\Settings\Application\UseCases\GetSettingsByGroup;use App\Modules\Settings\Application\UseCases\UpdateSetting;use App\Modules\Settings\Domain\Contracts\SettingsRepositoryInterface;use App\Modules\Settings\Domain\Exceptions\SettingsNotFoundException;use App\Modules\Settings\Presentation\Http\Requests\UpdateSettingsRequest;use Illuminate\Http\JsonResponse;use Illuminate\Http\Request;
class SettingsController extends Controller
{
 public function __construct(private readonly SettingsRepositoryInterface $settings){}
 public function index(Request $r):JsonResponse{$this->allow($r,'settings.view');return response()->json(['data'=>SettingsData::fromModels($this->settings->getAll())->toArray()]);}
 public function group(Request $r,string $group,GetSettingsByGroup $u):JsonResponse{$this->allow($r,'settings.view');return response()->json(['data'=>SettingsData::fromModels($u->execute($group))->toArray()]);}
 public function show(Request $r,string $key,GetSetting $u):JsonResponse{$this->allow($r,'settings.view');$s=$this->settings->findByKey($key);if($s===null)throw new SettingsNotFoundException($key);return response()->json(['data'=>SettingData::fromModel($s)->toArray()]);}
 public function update(UpdateSettingsRequest $r,UpdateSetting $u):JsonResponse{return response()->json(['data'=>SettingData::fromModel($u->execute(SettingData::fromArray($r->validated())))->toArray()]);}
 private function allow(Request $r,string $p):void{abort_unless($r->user()?->hasPermission($p),403);}
}
