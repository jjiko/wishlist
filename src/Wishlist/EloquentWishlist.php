<?php

namespace Jiko\Wishlist;

use Illuminate\Database\Eloquent\Model;

class EloquentWishlist extends Model {
  function __construct(array $attributes = []) {

    $this->table = config('wishlist.table');
    parent::__construct($attributes);
  }
}