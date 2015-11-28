Amazon Wishlist Scraper
==================
* Rewrite of justin scarpetti's "wishlister" to PSR-4

## Installation ##

    "require": {
      "jjiko/amazon-wishlist-scraper": "~3.0"
    }

    composer update

## Output JSON data ##

    $wishlist = new Wishlist([
        'id' => 'YOUR_WISHLIST_ID'
    ]);
    header('Content-Type: application/json');
    echo $wishlist->out('json');

## Config ##
* Using https://github.com/vlucas/phpdotenv .. see .env.example. 
* The .env file should exist in your project's root directory.. probably ../vendor

## Options ##

** id: your wishlist id **

** filter: unpurchased **
(or "reveal" in URL)
other values: purchased, all, price-drop

** sort: date-added **
other values:
1. universal-title
1. universal-price (price low to high)
1. universal-price-desc (price high to low)
1. priority (priority high to low)

## Navigate Wishlist object ##

    $wishlist = new Wishlist([
        'id' => 'YOUR_WISHLIST_ID'
    ])
      ->out;
    foreach($wishlist->items as $item) {
      echo $item->name; 
      echo $item->link; // etc
    }
    
API
Wishlist

Wishlist\Collection

    /**
     * Collection constructor.
     * Accepts id | [id, reveal, sort]
     * @param $params
     */
    function __construct($params)
      
    /**
     * @return array of wishlist items
     */
    public function all()
    
    
Wishlist\Item

    public $name;
    public $link;
    public $price;
    public $created_at;
    public $priority;
    public $ratings;
    public $comment;
    public $picture;
    public $page;
    public $ASIN;
    public $lgSecureImage;
    public $affiliateLink;

