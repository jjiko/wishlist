<?php

namespace Jiko\Wishlist;

class Item
{
  public $name;
  public $link;
  public $price;
  public $created_at;
  public $priority;
  public $ratingCount;
  public $ratingValue;
  public $comment;
  public $picture;
  public $page;
  public $ASIN;
  public $lgSecureImage;
  public $affiliateLink;

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
