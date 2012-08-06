<?php

//TODO: implement this
Class RSS {

  /**
   *
   * Builds an RSS feed from an array of elements
   * @param $items array of items to display in feed
   * @param $add_prefix boolean - adds a ':' to each
   * @return RSS XML String
   *
   */
  public static function buildRSSFromArray(array $items = array())
  {
    $items = array(); //title, description array
    $xml = new SimpleXMLElement('<rss version="2.0"></rss>');
    $channel = $xml->addChild('channel');
     
    //Create Channel
    $channel->addChild('title', 'Test Foo'); //Friendly Name
    $channel->addChild('link', ''); //Host Name
    $channel->addChild('description', '');
    $channel->addChild('pubDate', date(DATE_RFC2822));
     
    //Create Items
    foreach ($items as $item) {
      $newItem = $channel->addChild('item');
      $newItem->addChild('title', $item['title']);
      $newItem->addChild('description', $item['description']);
    }
    header('Content-Type: application/xhtml+xml');
    echo $xml->asXML();

  }

}