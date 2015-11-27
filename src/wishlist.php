<?php namespace Jiko\Amazon;

define('DEFAULT_AMAZON_WISHLIST_ID', env('AMAZON_WISHLIST_ID', 'YOUR_ID_HERE'));

require_once './wishlist/wishlist.php';
require_once './wishlist/wishlistItem.php';

class Wishlist
{
  protected $filter;
  protected $id;
  protected $wishlist;

  /**
   * Wishlist constructor.
   * @param null $params
   */
  function __construct($params = null)
  {
    set_time_limit(30);
    $this->set($params);

    if (empty($this->filter)) {
      $this->filter = "unpurchased";
    }
  }

  /**
   * @return mixed
   */
  protected function toJson() {
    return $this->wishlist->toJSON();
  }

  /**
   * @return mixed
   */
  protected function toXml() {
    return $this->wishlist->toXML();
  }

  /**
   * @return mixed
   */
  protected function toRss() {
    return $this->wishlist->toRSS();
  }

  /**
   * @return mixed
   */
  protected function toArray() {
    return $this->wishlist->toArray();
  }

  /**
   * @return mixed
   */
  protected function toObject() {
    return $this->wishlist->data;
  }

  /**
   * @param string $format
   * @return mixed
   */
  public function out($format="json") {
    if(method_exists($this, "to".ucfirst($format))) {
      $this->out{ucfirst($format)}();
    }

    // default to object
    return $this->wishlist;
  }

  function __destruct() {}

  /**
   * @param $params
   */
  public function set($params)
  {
    if ($params) {
      if (!is_array($params)) {
        $this->id = $params;
      } else {
        foreach ($params as $key => $param) {
          $this[$key] = $param;
        }
      }
    }
  }

  /**
   *
   */
  public function get()
  {
    $this->wishlist = new Wishlist();
  }
}