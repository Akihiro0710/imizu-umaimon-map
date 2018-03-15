<?php

use LINE\LINEBot\Event\MessageEvent;
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
$bot->addListener(function ($event) use ($bot) {
  $data = json_decode(file_get_contents(__DIR__ . '/umaimon.json'), true);
  $keys = array_keys($data);
  if (!($event instanceof MessageEvent)) {
    return true;
  }
  if (!($event instanceof TextMessage)) {
    return false;
  }
  $text = $event->getText();
  if (in_array($text, $keys)) {
    $content = $data[$text];
    $messageBuilder = showShopData($content);
    $bot->replyMessage($event->getReplyToken(), $messageBuilder);
  } else {
    switch ($text) {
      case 'うまいもん':
        $content = $data[$keys[mt_rand(0, count($keys - 1))]];
        $messageBuilder = showShopData($content);
        break;
      default:
        $messageBuilder = (new MultiMessageBuilder())
            ->add(new TextMessageBuilder('「うまいもん」と呼びかけて下さいね！'))
            ->add(new StickerMessageBuilder(1, 4));
    }
  }
  $bot->replyMessage($event->getReplyToken(), $messageBuilder);
  return false;
});
$bot->execute();