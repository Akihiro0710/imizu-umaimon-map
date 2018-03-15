<?php

use LINE\LINEBot\Event\MessageEvent;
use LINE\LINEBot\Event\MessageEvent\TextMessage;
use LINE\LINEBot\MessageBuilder\LocationMessageBuilder;
use LINE\LINEBot\MessageBuilder\MultiMessageBuilder;
use LINE\LINEBot\MessageBuilder\StickerMessageBuilder;
use LINE\LINEBot\MessageBuilder\TextMessageBuilder;

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/Bot.php';

$bot = new Bot();

foreach ($bot->parseEvent() as $event) {
  if (!($event instanceof MessageEvent)) {
    continue;
  }
  if (!($event instanceof TextMessage)) {
    continue;
  }
  $profile = $bot->getProfile($event->getUserId())->getJSONDecodedBody();
  $displayName = $profile['displayName'];
  $messageBuilder = new MultiMessageBuilder();
  switch ($event->getText()) {
    case 'こんにちは':
      $messageBuilder = $messageBuilder
          ->add(new StickerMessageBuilder(1, 17))
          ->add(new TextMessageBuilder("こんにちは！{$displayName}さん"));
      break;
    case 'こんばんは':
      $messageBuilder = $messageBuilder
          ->add(new StickerMessageBuilder(1, 17))
          ->add(new TextMessageBuilder("こんにちは！{$displayName}さん"));
      break;
    case 'うまいもん':
      $messageBuilder = $messageBuilder
          ->add(new TextMessageBuilder('うまいもんを紹介します'))
          ->add(new LocationMessageBuilder('射水市役所', '富山県射水市新開発４１０−１', 36.730544, 137.075451));
      break;
    default:
      $messageBuilder = $messageBuilder
          ->add(new TextMessageBuilder('「こんにちは」と呼びかけて下さいね！'))
          ->add(new StickerMessageBuilder(1, 4));
  }
  $bot->replyMessage($event->getReplyToken(), $messageBuilder);
}

