<?php

use App\Providers\AppServiceProvider;
use App\Providers\NativeServiceProvider;
use App\Providers\HorizonServiceProvider;
use App\Providers\Filament\AdminPanelProvider;

return [
    AppServiceProvider::class,
    AdminPanelProvider::class,
    HorizonServiceProvider::class,
    NativeServiceProvider::class,
];
