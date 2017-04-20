<?php

namespace Jiko\Wishlist\Providers;

use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;

class WishlistServiceProvider extends ServiceProvider
{
  public function boot()
  {
    parent::boot();
    $this->loadViewsFrom(__DIR__ . '/../resources/views', 'wishlist');
  }

  public function map()
  {
    require_once(__DIR__ . '/../Http/routes.php');
  }
}