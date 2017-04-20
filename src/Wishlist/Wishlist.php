<?php namespace Jiko\Wishlist;

define('DEFAULT_AMAZON_WISHLIST_ID', env('AMAZON_WISHLIST_ID', 'YOUR_ID_HERE'));
define('DEFAULT_AMAZON_AFFILIATE_TAG', env('AMAZON_TRACKING_ID', 'YOUR_AFFILIATE_TAG_HERE'));
define('DEFAULT_WISHLIST_FILTER', 'unpurchased');
define('DEFAULT_WISHLIST_SORT', 'date-added');

class Wishlist
{
  protected $filter;
  protected $sort;
  protected $wishlist;

  public $id;

  /**
   * Wishlist constructor.
   * @param null $params
   */
  function __construct($params = null)
  {
    $this->set($params);

    if (empty($this->filter)) {
      $this->filter = DEFAULT_WISHLIST_FILTER;
    }

    if(empty($this->sort)) {
      $this->sort = DEFAULT_WISHLIST_SORT;
    }

    $this->get();
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
  protected function toArray() {
    return $this->wishlist->toArray();
  }

  /**
   * @return mixed
   */
  protected function toObject() {
    return $this->wishlist->items;
  }

  /**
   * @param string $format
   * @return mixed
   */
  public function out($format=null) {
    if(!empty($format)) {
      $method = "to".ucfirst(strtolower($format));
      if(method_exists($this, $method)) {
        return $this->$method();
      }
    }

    return (object) [
      'id' => $this->id,
      'reveal' => $this->filter,
      'sort' => $this->sort,
      'items' => $this->wishlist->all()
    ];
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
          $this->{$key} = $param;
        }
      }
    }
  }

  /**
   *
   */
  public function get()
  {
    $params = (object) [
      'id' => $this->id,
      'reveal' => $this->filter,
      'sort' => $this->sort
    ];
    $this->wishlist = new Collection($params);
  }
}