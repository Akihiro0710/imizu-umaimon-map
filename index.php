<?php

use LINE\LINEBot\Event\MessageEvent;
use LINE\LINEBot\Event\MessageEvent\TextMessage;
use LINE\LINEBot\MessageBuilder\LocationMessageBuilder;
use LINE\LINEBot\MessageBuilder\MultiMessageBuilder;
use LINE\LINEBot\MessageBuilder\StickerMessageBuilder;
use LINE\LINEBot\MessageBuilder\TextMessageBuilder;

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/Bot.php';
function createLocation($data)
{
  $title = $data['name'];
  $address = $data['address'];
  $lat = $data['lat'];
  $lon = $data['lon'];
  return new LocationMessageBuilder($title, $address, $lat, $lon);
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
  $messageBuilder = new MultiMessageBuilder();
  $text = $event->getText();
  if (in_array($text, $keys)) {
    $content = $data[$text];
    $messageBuilder = $messageBuilder
        ->add(new TextMessageBuilder($content['name'] . PHP_EOL . $content['business_hours'] . PHP_EOL . $content['tel']))
        ->add(new TextMessageBuilder($content['summary']))
        ->add(createLocation($content));
    $bot->replyMessage($event->getReplyToken(), $messageBuilder);
    return false;
  }
  switch ($text) {
    case 'うまいもん':
      $messageBuilder = $messageBuilder
          ->add(new TextMessageBuilder('うまいもんを紹介します'))
          ->add(new LocationMessageBuilder('射水市役所', '富山県射水市新開発４１０−１', 36.730544, 137.075451));
      break;
    default:
      $messageBuilder = $messageBuilder
          ->add(new TextMessageBuilder('「うまいもん」と呼びかけて下さいね！'))
          ->add(new StickerMessageBuilder(1, 4));
  }
  $bot->replyMessage($event->getReplyToken(), $messageBuilder);
  return false;
});
$bot->execute();