<?php

namespace Jiko\Wishlist;

use Illuminate\Database\Eloquent\Model;
use Jiko\Api\CacheableApiTrait;

class EloquentWishlist extends Model {
  function __construct(array $attributes = []) {

    $this->table = config('wishlist.table');
    parent::__construct($attributes);
  }
}