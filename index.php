<?php

use LINE\LINEBot;
use LINE\LINEBot\Constant\HTTPHeader;
use LINE\LINEBot\Event\MessageEvent;
use LINE\LINEBot\Event\MessageEvent\TextMessage;
use LINE\LINEBot\HTTPClient\CurlHTTPClient;
use LINE\LINEBot\MessageBuilder\MultiMessageBuilder;
use LINE\LINEBot\MessageBuilder\StickerMessageBuilder;
use LINE\LINEBot\MessageBuilder\TextMessageBuilder;

require_once __DIR__ . '/vendor/autoload.php';

$httpClient = new CurlHTTPClient(getenv('CHANNEL_ACCESS_TOKEN'));
$bot = new LINEBot($httpClient, ['channelSecret' => getenv('CHANNEL_SECRET')]);
$signature = $_SERVER["HTTP_" . HTTPHeader::LINE_SIGNATURE];

$events = $bot->parseEventRequest(file_get_contents('php://input'), $signature);
foreach ($events as $event) {

  $profile = $bot->getProfile($event->getUserId())->getJSONDecodedBody();
  $displayName = $profile['displayName'];

  if ($event instanceof MessageEvent) {
    if ($event instanceof TextMessage) {
      if($event->getText() === 'こんにちは') {
        $bot->replyMessage($event->getReplyToken(),
          (new MultiMessageBuilder())
            ->add(new StickerMessageBuilder(1, 17))
            ->add(new TextMessageBuilder('こんにちは！' . $displayName . 'さん'))
        );
      } else {
        $bot->replyMessage($event->getReplyToken(),
          (new MultiMessageBuilder())
            ->add(new TextMessageBuilder('「こんにちは」と呼びかけて下さいね！'))
            ->add(new StickerMessageBuilder(1, 4))
        );
      }
    }
    continue;
  }
}

