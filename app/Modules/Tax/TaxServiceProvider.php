<?php

namespace App\Modules\Tax;

use App\Modules\Tax\Domain\Contracts\TaxCalculatorInterface;
use App\Modules\Tax\Infrastructure\Persistence\DatabaseTaxCalculator;
use Illuminate\Support\ServiceProvider;

final class TaxServiceProvider extends ServiceProvider
{
    public array $bindings = [TaxCalculatorInterface::class => DatabaseTaxCalculator::class];
}
