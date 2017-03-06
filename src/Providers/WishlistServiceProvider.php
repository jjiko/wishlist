<?php
namespace Jiko\Wishlist;

use Illuminate\Support\ServiceProvider;

class WishlistServiceProvider extends ServiceProvider
{
  public function register()
  {
    
  }

  public function map(Router $router) {
    require_once(__DIR__ . '/../Http/routes.php');
  }
}