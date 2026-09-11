<?php
namespace App\Modules\Customer;
use App\Modules\Customer\Domain\Contracts\CustomerRepositoryInterface; use App\Modules\Customer\Domain\Contracts\CustomerFeaturesRepositoryInterface; use App\Modules\Customer\Infrastructure\Persistence\EloquentCustomerRepository; use App\Modules\Customer\Infrastructure\Persistence\EloquentCustomerFeaturesRepository; use Illuminate\Support\ServiceProvider;
final class CustomerServiceProvider extends ServiceProvider { public array $bindings=[CustomerRepositoryInterface::class=>EloquentCustomerRepository::class,CustomerFeaturesRepositoryInterface::class=>EloquentCustomerFeaturesRepository::class]; }
