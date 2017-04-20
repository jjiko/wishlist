<?php

namespace Jiko\Wishlist\Http\Controllers;

use Jiko\Http\Controllers\Controller;
use Jiko\Wishlist\Wishlist;

class WishlistPageController extends Controller
{
  public function index()
  {
    $wishlist = new Wishlist([
      'id' => '10KWZ5ON6VU4N'
    ]);
    $collection = $wishlist->out();

    $this->page->title = "Wishlist";
    $this->content('wishlist::index', ['wid' => $collection->id, 'items' => $collection->items]);
  }
}