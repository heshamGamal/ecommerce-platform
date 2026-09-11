<?php
use App\Modules\Auth\AuthServiceProvider;
use App\Modules\Catalog\CatalogServiceProvider;
use App\Modules\Settings\SettingsServiceProvider;
use App\Providers\AppServiceProvider;
return [AppServiceProvider::class,AuthServiceProvider::class,CatalogServiceProvider::class,SettingsServiceProvider::class];
