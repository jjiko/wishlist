<?php

namespace Jiko\Wishlist;

// @todo find a namespaced phpQuery.. meh
use phpQuery;

class Collection
{
  static $base_url = "http://www.amazon.com";

  protected $items = [];
  protected $errors = [];

  /**
   * Collection constructor.
   * @param $params
   */
  function __construct($params)
  {
    $this->url = vsprintf(
      "%s/registry/wishlist/%s?reveal=%s&sort=%s&layout=standard",
      [static::$base_url, $params->id, $params->reveal, $params->sort]
    );

    try {
      if (!($this->content = $this->getContent())) {
        throw new Exception("Could not read wishlist from url: {$this->url}");
      }
    } catch (Exception $e) {
      $this->errors[] = $e;
    }

    $this->parseContent();

  }

  /**
   * @note watch the CURLOPT_CONNECTTIMEOUT on a shared host.
   *       Especially helpful if your server is shit or Amazon is down (god forbid)
   * @param $url
   * @return string
   */
  protected static function getContentFromURL($url)
  {
    $curl = curl_init();
    curl_setopt_array($curl, array
    (
      CURLOPT_URL => $url,
      CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.1 (KHTML, like Gecko) Chrome/22.0.1207.1 Safari/537.1',
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_CONNECTTIMEOUT => 30,
      CURLOPT_FOLLOWLOCATION => true,
      CURLOPT_HEADER => false
    ));

    $data = curl_exec($curl);
    curl_close($curl);

    return $data;
  }

  protected function getContent($page = 1)
  {
    return phpQuery::newDocument(static::getContentFromURL($this->url . "&page=$page"));
  }

  /**
   * Untested.. good luck :)
   */
  protected function parseV1Content()
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

  protected function parseV2Content()
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
          $itemInfo = pq($item)->find('div[id^="itemInfo_"]')->html();
          // (2,566)
          if(preg_match('/\(([\d,]+)\)/', $itemInfo, $matches)) {
            $rating_count = $matches[1];
          }

          //  4.6 out of 5
          if(preg_match('/([\d]+[\.]?[^\s]+) out of/', $itemInfo, $matches)) {
            $rating_value = $matches[1];
          }

          $priority = trim(pq($item)->find('span[id^="itemPriorityLabel_"]')->html());

          $current_item = new Item([
            'name' => $name,
            'link' => static::$base_url . $link,
            'price' => trim(pq($item)->find('span[id^="itemPrice_"]')->html()),
            'created_at' => trim(str_replace('Added', '', pq($item)->find('div[id^="itemAction_"] .a-size-small')->html())),
            'priority' => empty($priority) ? "Medium" : $priority,
            'ratingValue' => '',
            'ratingCount' => '',
            'comment' => trim((pq($item)->find('span[id^="itemComment_"]')->html())),
            'picture' => pq($item)->find('div[id^="itemImage_"] img')->attr('src'),
            'page' => $pi
          ]);
          $current_item->set([
            'ASIN' => static::getASIN($current_item->link),
            'lgSecureImage' => static::getLgSecureImage($current_item->picture)
          ]);
          $current_item->set([
            'affiliateLink' => static::getAffiliateLink($current_item->ASIN)
          ]);
        }
        $this->items[] = $current_item;
      }
    }
  }

  /**
   * Pass to the correct DOM selectors
   *
   * @return string|void
   */
  protected function parseContent()
  {
    if (!empty($this->errors)) {
      return $this->getErrors();
    }

    try {
      $items = pq('tbody.itemWrapper');
      if ($items->html()) {
        return $this->parseV1Content();
      }
      return $this->parseV2Content();
    } catch (Exception $e) {
      $this->errors[] = $e;
    }
  }

  /**
   * Used in creating product and affiliate links
   *
   * @param $url
   * @return mixed|string
   */
  protected function getASIN($url)
  {
    $ASIN = str_replace(static::$base_url . "/dp/", '', $url);
    $ASIN = substr($ASIN, 0, 10);
    return $ASIN;
  }

  /**
   * @param $image_url
   * @return string
   */
  protected function getLgSecureImage($image_url)
  {
    $largeSSLImage = str_replace("http://ecx.images-amazon.com", 'https://images-na.ssl-images-amazon.com', $image_url);
    $largeSSLImage = str_replace("_.jpg", '0_.jpg', $largeSSLImage);
    return $largeSSLImage;
  }

  /**
   * @param $ASIN
   * @return string
   */
  protected function getAffiliateLink($ASIN)
  {
    $affiliateURL = vsprintf(
      "%s/dp/%s/ref=nosim?tag=%s",
      [static::$base_url, $ASIN, DEFAULT_AMAZON_AFFILIATE_TAG]
    );
    return $affiliateURL;
  }

  /**
   * @return array|string
   */
  public function toJSON()
  {
    if (!empty($this->errors)) {
      return $this->errors;
    }
    return json_encode($this->items);
  }

  /**
   * @return string
   */
  protected function getErrors()
  {
    $response = (object)['errors' => []];
    foreach ($this->errors as $e) {
      $response->errors[] = $e->getMessage();
    }
    return json_encode($response);
  }

  /**
   * @return array of wishlist items
   */
  public function all()
  {
    return $this->items;
  }

  public function meta()
  {
    return new stdClass([
      'id' => $this->id,
      'link' => vsprintf("%s/registry/wishlist/%s", [static::$base_url, $this->id])
    ]);
  }
}