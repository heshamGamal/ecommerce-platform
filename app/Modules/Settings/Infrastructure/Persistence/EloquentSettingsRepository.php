<?php
namespace App\Modules\Settings\Infrastructure\Persistence;
use App\Models\Setting;use App\Modules\Settings\Application\DTOs\SettingData;use App\Modules\Settings\Domain\Contracts\SettingsRepositoryInterface;use Illuminate\Support\Collection;
class EloquentSettingsRepository implements SettingsRepositoryInterface
{
 public function findByKey(string $key):?Setting{return Setting::query()->where('key',$key)->first();}
 public function getByGroup(string $group):Collection{return Setting::query()->where('group',$group)->orderBy('key')->get();}
 public function getAll():Collection{return Setting::query()->orderBy('group')->orderBy('key')->get();}
 public function save(SettingData $d):Setting
 {
  $s=Setting::query()->firstOrNew(['key'=>$d->key]);$s->group=$d->group;$s->type=$d->type;$s->description=$d->description;$s->setTypedValue($d->value);$s->save();return $s->refresh();
 }
 public function delete(string $key):bool{return Setting::query()->where('key',$key)->delete()>0;}
}
