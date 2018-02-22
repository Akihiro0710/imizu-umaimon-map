<?php

use LINE\LINEBot\Event\MessageEvent;
use LINE\LINEBot\Event\MessageEvent\TextMessage;
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

  if ($event->getText() === 'こんにちは') {
    $profile = $bot->getProfile($event->getUserId())->getJSONDecodedBody();
    $displayName = $profile['displayName'];
    $messageBuilder = (new MultiMessageBuilder())
        ->add(new StickerMessageBuilder(1, 17))
        ->add(new TextMessageBuilder('こんにちは！' . $displayName . 'さん'));
  } else {
    $messageBuilder = (new MultiMessageBuilder())
        ->add(new TextMessageBuilder('「こんにちは」と呼びかけて下さいね！'))
        ->add(new StickerMessageBuilder(1, 4));
  }
  $bot->replyMessage($event->getReplyToken(), $messageBuilder);
}

