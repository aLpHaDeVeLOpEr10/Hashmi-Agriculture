<?php

return [

    'name' => env('APP_NAME', 'Hashmi Agriculture'),

    'tagline' => 'Crop & Silage Costs',

    'currency' => 'PKR',

    'number_grouping' => env('AGRI_NUMBER_GROUPING', 'international'),

    'navigation' => [
        ['label' => 'Dashboard', 'route' => 'dashboard', 'match' => 'dashboard', 'group' => 'Overview', 'icon' => 'layout-dashboard'],
        ['label' => 'Crop Seasons', 'route' => 'seasons.index', 'match' => ['seasons.*', 'stages.*', 'expenses.*'], 'group' => 'Crop & Silage', 'icon' => 'sprout'],
        ['label' => 'Payables', 'route' => 'payables.index', 'match' => 'payables.*', 'group' => 'Crop & Silage', 'icon' => 'receipt'],
        ['label' => 'Production', 'route' => 'production.index', 'match' => 'production.*', 'group' => 'Crop & Silage', 'icon' => 'warehouse'],
        ['label' => 'Machinery', 'route' => 'machinery.index', 'match' => 'machinery.*', 'group' => 'Crop & Silage', 'icon' => 'tractor'],
        ['label' => 'Recurring costs', 'route' => 'recurring.index', 'match' => 'recurring.*', 'group' => 'Crop & Silage', 'icon' => 'repeat'],
        ['label' => 'Reports', 'route' => 'reports.index', 'match' => 'reports.*', 'group' => 'Analysis', 'icon' => 'chart-column'],
        ['label' => 'Livestock', 'route' => 'preview.livestock', 'match' => 'preview.livestock', 'group' => 'Upcoming modules', 'icon' => 'beef', 'preview' => true],
        ['label' => 'Inventory & Feed', 'route' => 'preview.inventory', 'match' => 'preview.inventory', 'group' => 'Upcoming modules', 'icon' => 'package', 'preview' => true],
        ['label' => 'Employees', 'route' => 'preview.employees', 'match' => 'preview.employees', 'group' => 'Upcoming modules', 'icon' => 'users', 'preview' => true],
        ['label' => 'Settings', 'route' => 'settings.index', 'match' => 'settings.*', 'group' => 'System', 'icon' => 'settings'],
    ],

];
