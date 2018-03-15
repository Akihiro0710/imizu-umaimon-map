<?php

use LINE\LINEBot\Event\MessageEvent;
use LINE\LINEBot\Event\MessageEvent\LocationMessage;
use LINE\LINEBot\Event\MessageEvent\TextMessage;
use LINE\LINEBot\MessageBuilder\LocationMessageBuilder;
use LINE\LINEBot\MessageBuilder\MultiMessageBuilder;
use LINE\LINEBot\MessageBuilder\StickerMessageBuilder;
use LINE\LINEBot\MessageBuilder\TextMessageBuilder;

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/Bot.php';
function showShopData($data)
{
  $title = $data['name'];
  $summary = $data['summary'];
  $business_hours = $data['business_hours'];
  $tel = $data['tel'];
  $address = $data['address'];
  $lat = $data['lat'];
  $lon = $data['lon'];
  return (new MultiMessageBuilder())
      ->add(new TextMessageBuilder(implode(PHP_EOL, [$title, $business_hours, $tel])))
      ->add(new TextMessageBuilder($summary))
      ->add(new LocationMessageBuilder($title, $address, $lat, $lon));
}

$bot = new Bot();
$data = json_decode(file_get_contents(__DIR__ . '/umaimon.json'), true);
$bot->addListener(function ($event) use ($data, $bot) {
  if (!($event instanceof MessageEvent)) {
    return;
  }
  if ($event instanceof LocationMessage) {
    $evLat = $event->getLatitude();
    $evLon = $event->getLongitude();
    $distances = [];
    foreach ($data as $key => $value) {
      $lat = $value['lat'];
      $lon = $value['lon'];
      $distances[$key] = $lat . ',' . $lon;
    }
//    asort($distances);
    $key = array_keys($distances)[0];
    $shop = $data[$key];
    $messageBuilder = (new MultiMessageBuilder())
        ->add(new TextMessageBuilder($shop['name'] . 'との距離は' . $distances[$key]))
        ->add(new TextMessageBuilder(var_export($distances, true)));
    $bot->replyMessage($event->getReplyToken(), $messageBuilder);
    return;
  }
  if (!($event instanceof TextMessage)) {
    return;
  }
  $text = $event->getText();
  $keys = array_keys($data);
  switch ($text) {
    case 'うまいもん':
      $content = $data[$keys[mt_rand(0, count($keys) - 1)]];
      $messageBuilder = showShopData($content);
      break;
    default:
      if (in_array($text, $keys)) {
        $content = $data[$text];
        $messageBuilder = showShopData($content);
      } else {
        $messageBuilder = (new MultiMessageBuilder())
            ->add(new TextMessageBuilder('「うまいもん」と呼びかけて下さいね！'))
            ->add(new StickerMessageBuilder(1, 4));
      }
  }
  $bot->replyMessage($event->getReplyToken(), $messageBuilder);
  return;
});
$bot->execute();