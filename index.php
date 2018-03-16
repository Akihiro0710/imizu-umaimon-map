<?php

use LINE\LINEBot\Event\BaseEvent;
use LINE\LINEBot\Event\MessageEvent;
use LINE\LINEBot\Event\MessageEvent\LocationMessage;
use LINE\LINEBot\Event\MessageEvent\TextMessage;
use LINE\LINEBot\Event\PostbackEvent;
use LINE\LINEBot\MessageBuilder\ImageMessageBuilder;
use LINE\LINEBot\MessageBuilder\LocationMessageBuilder;
use LINE\LINEBot\MessageBuilder\MultiMessageBuilder;
use LINE\LINEBot\MessageBuilder\StickerMessageBuilder;
use LINE\LINEBot\MessageBuilder\TemplateBuilder\ButtonTemplateBuilder;
use LINE\LINEBot\MessageBuilder\TemplateMessageBuilder;
use LINE\LINEBot\MessageBuilder\TextMessageBuilder;
use LINE\LINEBot\TemplateActionBuilder\MessageTemplateActionBuilder;
use LINE\LINEBot\TemplateActionBuilder\PostbackTemplateActionBuilder;
use LINE\LINEBot\TemplateActionBuilder\UriTemplateActionBuilder;

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/Bot.php';


$bot = new Bot();
$data = json_decode(file_get_contents(__DIR__ . '/umaimon.json'), true);

function showShopData(Bot $bot, BaseEvent $event, $data, $key)
{
  $shop = $data[$key];
  $shop['id'] = $key;
  $title = $shop['name'];
  $summary = $shop['summary'];
  $messageBuilder = new TemplateMessageBuilder(
      $title,
      new ButtonTemplateBuilder(
          $title,
          substr($summary, 0, 5) . '…',
          "https://{$_SERVER["HTTP_HOST"]}/images/{$key}.jpg",
          [
              new PostbackTemplateActionBuilder('詳細を見る', $key)
          ]
      )
  );
  $bot->replyMessage($event->getReplyToken(), $messageBuilder);
}

function showShopDetail(Bot $bot, BaseEvent $event, $data, $key)
{
  $shop = $data[$key];
  $shop['id'] = $key;
  $title = $shop['name'];
  $summary = $shop['summary'];
  $business_hours = $shop['business_hours'];
  $tel = $shop['tel'];
  $address = $shop['address'];
  $lat = $shop['lat'];
  $lon = $shop['lon'];
  $alt = <<<EOT
$title
営業時間：$business_hours
電話番号：$tel
住所：$address
EOT;
  $image = "https://" . $_SERVER["HTTP_HOST"] . '/images/' . $key;
  $messageBuilder = (new MultiMessageBuilder())
      ->add(new TemplateMessageBuilder(
          $alt,
          new ButtonTemplateBuilder(
              $title,
              $business_hours,
              $image . '.jpg',
              [
                  new UriTemplateActionBuilder($tel, 'tel:' . $tel),
              ]
          )
      ))
      ->add(new TextMessageBuilder($summary))
      ->add(new LocationMessageBuilder($title, $address, $lat, $lon));
  $bot->replyMessage($event->getReplyToken(), $messageBuilder);
}

$bot->addListener(function ($event) use ($data, $bot) {
  if (!$event instanceof PostbackEvent) {
    return false;
  }
  $key = $event->getPostbackData();
  showShopDetail($bot, $event, $data, $key);
  return true;
});
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
      $distances[$key] = sqrt(($lat - $evLat) ** 2 + ($lon - $evLon) ** 2);
    }
    asort($distances);
    $key = array_keys($distances)[0];
    showShopData($bot, $event, $data, $key);
    return;
  }
  if (!($event instanceof TextMessage)) {
    return;
  }
  $text = $event->getText();
  $keys = array_keys($data);
  switch ($text) {
    case 'うまいもん':
      $key = $keys[mt_rand(0, count($keys) - 1)];
      showShopData($bot, $event, $data, $key);
      break;
    default:
      if (in_array($text, $keys)) {
        showShopData($bot, $event, $data, $text);
      } else {
        $messageBuilder = (new MultiMessageBuilder())
            ->add(new TextMessageBuilder('「うまいもん」と呼びかけて下さいね！'))
            ->add(new StickerMessageBuilder(1, 4));
        $bot->replyMessage($event->getReplyToken(), $messageBuilder);
      }
  }
  return;
});
$bot->execute();