<?php Jiko\Amazon\Wishlist;

class Collection
{
  static $base_url = "http://www.amazon.com";

  protected $items = [];

  public function __construct($wid, $reveal, $sort)
  {
    $this->url = vsprintf(
      "%s/registry/wishlist/%s?reveal=%s&sort=%s&layout=standard",
      [static::$base_url, $wid, $reveal, $sort]
    );

    try {
      if (!($this->content = $this->getContent())) {
        throw new Exception("Could not read wishlist from url: {$this->url}");
      }
    } catch (Exception $e) {
      // $e->getMessage();
    }

    $this->parseContent();

  }

  public function getContent($page = 1)
  {
    $this->content = phpQuery::newDocumentFile($this->url . "&page=$page");
  }

  public function parseV1Content()
  {
    $pages = count(pq('.pagDiv .pagPage'));
    $items = pq('tbody.itemWrapper');
    foreach ($items as $item) {
      $check_if_regular = pq($item)->find('span.commentBlock nobr');

      if ($check_if_regular != '') {
//$array[$i]['array'] = pq($item)->html();
        $array[$i]['num'] = $i + 1;
        $array[$i]['name'] = text_prepare(pq($item)->find('span.productTitle strong a')->html());
        $array[$i]['link'] = pq($item)->find('span.productTitle a')->attr('href');
        $array[$i]['old-price'] = pq($item)->find('span.strikeprice')->html();
        $array[$i]['new-price'] = text_prepare(pq($item)->find('span[id^="itemPrice_"]')->html());
        $array[$i]['date-added'] = text_prepare(str_replace('Added', '', pq($item)->find('span.commentBlock nobr')->html()));
        $array[$i]['priority'] = pq($item)->find('span.priorityValueText')->html();
        $array[$i]['rating'] = pq($item)->find('span.asinReviewsSummary a span span')->html();
        $array[$i]['total-ratings'] = pq($item)->find('span.crAvgStars a:nth-child(2)')->html();
        $array[$i]['comment'] = text_prepare(pq($item)->find('span.commentValueText')->html());
        $array[$i]['picture'] = pq($item)->find('td.productImage a img')->attr('src');
        $array[$i]['page'] = $page_num;
        $array[$i]['ASIN'] = get_ASIN($array[$i]['link']);
        $array[$i]['large-ssl-image'] = get_large_ssl_image($array[$i]['picture']);
        $array[$i]['affiliate-url'] = get_affiliate_link($array[$i]['ASIN']);

        $i++;
      }
    }
  }

  public function parseV2Content()
  {
    $pages = count(pq('#wishlistPagination li[data-action="pag-trigger"]'));
    if (empty($pages)) $pages = 1;

    for ($pi = 1; $pi <= $pages; $pi++) {

      if (!($content = $this->getContent($pi))) {
        throw new Exception("Could not read wishlist from url: {$this->url}");
      }

      $items = pq('.g-items-section div[id^="item_"]');

      //loop through items
      foreach ($items as $i => $item) {

        $name = htmlentities(trim(pq($item)->find('a[id^="itemName_"]')->html()));
        $link = pq($item)->find('a[id^="itemName_"]')->attr('href');

        if (!empty($name) && !empty($link)) {

          $total_ratings = pq($item)->find('div[id^="itemInfo_"] div:a-spacing-small:first a.a-link-normal:last')->html();
          $total_ratings = trim(str_replace(array('(', ')'), '', $total_ratings));
          $total_ratings = is_numeric($total_ratings) ? $total_ratings : '';

          $current_item = new WishlistItem([
            'name' => $name,
            'link' => static::$base_url . $link,
            'newPrice' => trim(pq($item)->find('span[id^="itemPrice_"]')->html()),
            'dateAdded' => trim(str_replace('Added', '', pq($item)->find('div[id^="itemAction_"] .a-size-small')->html())),
            'priority' => trim(pq($item)->find('span[id^="itemPriorityLabel_"]')->html()),
            'totalRatings' => $total_ratings,
            'comment' => trim((pq($item)->find('span[id^="itemComment_"]')->html())),
            'picture' => pq($item)->find('div[id^="itemImage_"] img')->attr('src'),
            'page' => $pi
          ]);
          $current_item->set([
            'ASIN' => static::getASIN($this->link),
            'lgSecureImage' => static::getLgSecureImage($current_item->picture)
          ]);
          $current_item->set([
            'affiliateLink' => static::getAffiliateLink($current_item->ASIN)
          ]);
        }
      }
    }
  }

  public function parseContent()
  {
    try {
      $items = pq('tbody.itemWrapper');
      if ($items->html()) {
        return $this->parseV1Content();
      }
      return $this->parseV2Content();
    } catch (Exception $e) {
      // $e->getMessage();
    }
  }

  protected function getASIN($url)
  {
    $ASIN = str_replace(static::$base_url . "/dp/", '', $url);
    $ASIN = substr($ASIN, 0, 10);
    return $ASIN;
  }

  function getLgSecureImage($image_url)
  {
    $largeSSLImage = str_replace("http://ecx.images-amazon.com", 'https://images-eu.ssl-images-amazon.com', $image_url);
    $largeSSLImage = str_replace("_.jpg", '0_.jpg', $largeSSLImage);
    return $largeSSLImage;
  }
}