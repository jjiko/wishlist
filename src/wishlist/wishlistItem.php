<?php namespace Jiko\Amazon\Wishlist;
class Item
{
  /**
   * WishlistItem constructor.
   */
  function __construct($params)
  {
    $this->set($params);
  }

  public function set($params)
  {
    if(is_array($params)) {
      foreach($params as $key => $value) {
        $this->{$key} = $value;
      }
    }
  }
}
