<?php
namespace App\Modules\Promotion\Presentation\Http\Requests;
use App\Modules\Auth\Presentation\Http\Concerns\AuthorizesRequest;
use Illuminate\Foundation\Http\FormRequest;
final class PromotionManagementRequest extends FormRequest
{
    use AuthorizesRequest;
    public function authorize(): bool { return $this->authorizePermission(str_ends_with((string) $this->route()?->getName(), '.index') || str_ends_with((string) $this->route()?->getName(), '.show') ? 'promotions.view' : (str_ends_with((string) $this->route()?->getName(), '.destroy') ? 'promotions.delete' : (str_ends_with((string) $this->route()?->getName(), '.update') ? 'promotions.update' : 'promotions.create'))); }
    public function rules(): array { return ['code' => ['sometimes','string','max:80','regex:/^[A-Za-z0-9_-]+$/'], 'type' => ['sometimes','in:percent,fixed'], 'value' => ['sometimes','integer','min:1'], 'minimum_order_amount' => ['sometimes','integer','min:0'], 'usage_limit' => ['nullable','integer','min:1'], 'per_user_limit' => ['nullable','integer','min:1'], 'starts_at' => ['nullable','date'], 'ends_at' => ['nullable','date','after_or_equal:starts_at'], 'is_active' => ['sometimes','boolean']]; }
}
