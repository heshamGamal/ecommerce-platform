<?php
use App\Modules\Auth\AuthServiceProvider;
use App\Modules\Catalog\CatalogServiceProvider;
use App\Modules\Customer\CustomerServiceProvider;
use App\Modules\Settings\SettingsServiceProvider;
use App\Modules\Staff\StaffServiceProvider;
use App\Providers\AppServiceProvider;
return [AppServiceProvider::class,AuthServiceProvider::class,CatalogServiceProvider::class,CustomerServiceProvider::class,SettingsServiceProvider::class,StaffServiceProvider::class];
