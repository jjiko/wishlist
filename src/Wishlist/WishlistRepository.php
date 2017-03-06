<?php

namespace Jiko\Wishlist;

use Jiko\Api\CacheableApiTrait;

class WishlistRepository {
  use CacheableApiTrait;

  protected $wishlist;

  function __construct() {
    $this->wishlist = new Wishlist();
  }
}