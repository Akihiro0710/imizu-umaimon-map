<?php

use LINE\LINEBot\Event\BaseEvent;
use LINE\LINEBot\Event\MessageEvent;
use LINE\LINEBot\Event\MessageEvent\LocationMessage;
use LINE\LINEBot\Event\MessageEvent\TextMessage;
use LINE\LINEBot\Event\PostbackEvent;
use LINE\LINEBot\MessageBuilder\LocationMessageBuilder;
use LINE\LINEBot\MessageBuilder\MultiMessageBuilder;
use LINE\LINEBot\MessageBuilder\StickerMessageBuilder;
use LINE\LINEBot\MessageBuilder\TemplateBuilder\ButtonTemplateBuilder;
use LINE\LINEBot\MessageBuilder\TemplateMessageBuilder;
use LINE\LINEBot\MessageBuilder\TextMessageBuilder;

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/Bot.php';


$bot = new Bot();
function showShopsData(Bot $bot, BaseEvent $event, $text, $keys)
{
  $key = $keys[0];
  $messageBuilder = new TemplateMessageBuilder(
      $text,
      new ButtonTemplateBuilder(...$bot->createShopDataParams($key))
  );
  $bot->replyMessage($event->getReplyToken(), $messageBuilder);
}

function showShopData(Bot $bot, BaseEvent $event, $key)
{
  $messageBuilder = new TemplateMessageBuilder(
      $bot->data[$key]['name'],
      new ButtonTemplateBuilder(...$bot->createShopDataParams($key))
  );
  $bot->replyMessage($event->getReplyToken(), $messageBuilder);
}

function showShopDetail(Bot $bot, BaseEvent $event, $key)
{
  $shop = $bot->data[$key];
  $title = $shop['name'];
  $summary = $shop['summary'];
  $business_hours = $shop['business_hours'];
  $tel = $shop['tel'];
  $address = $shop['address'];
  $lat = $shop['lat'];
  $lon = $shop['lon'];
  $description = <<<EOT
$title
＝＝＝＝＝＝＝＝＝＝
営業時間：$business_hours
電話番号：$tel
住所：$address

$summary
EOT;
  $messageBuilder = (new MultiMessageBuilder())
      ->add(new TextMessageBuilder($description))
      ->add(new LocationMessageBuilder($title, $address, $lat, $lon));
  $bot->replyMessage($event->getReplyToken(), $messageBuilder);
}

$bot->addListener(function ($event) use ($bot) {
  if (!$event instanceof PostbackEvent) {
    return false;
  }
  $key = $event->getPostbackData();
  showShopDetail($bot, $event, $key);
  return true;
});

$bot->addListener(function ($event) use ($bot) {
  if (!($event instanceof MessageEvent)) {
    return;
  }
  if ($event instanceof LocationMessage) {
    $evLat = $event->getLatitude();
    $evLon = $event->getLongitude();
    $distances = [];
    foreach ($bot->data as $key => $value) {
      $lat = $value['lat'];
      $lon = $value['lon'];
      $distances[$key] = sqrt(($lat - $evLat) ** 2 + ($lon - $evLon) ** 2);
    }
    asort($distances);
    showShopsData($bot, $event, '近場のうまいもんを紹介するよ', array_keys($distances));
    return;
  }
  if (!($event instanceof TextMessage)) {
    return;
  }
  $data = $bot->data;
  $text = $event->getText();
  $keys = array_keys($data);
  shuffle($keys);
  switch ($text) {
    case 'うまいもん':
      showShopsData($bot, $event, 'うまいもんをランダムに紹介するよ', $keys);
      break;
    default:
      if (in_array($text, $keys)) {
        showShopData($bot, $event, $text);
      } else {
        $messageBuilder = (new MultiMessageBuilder())
            ->add(new TextMessageBuilder('「うまいもん」と呼びかけて下さいね！'))
            ->add(new StickerMessageBuilder(1, 4));
        $bot->replyMessage($event->getReplyToken(), $messageBuilder);
      }
  }
});
$bot->execute();