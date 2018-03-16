<?php

use LINE\LINEBot;
use LINE\LINEBot\Constant\HTTPHeader;
use LINE\LINEBot\HTTPClient\CurlHTTPClient;
use LINE\LINEBot\MessageBuilder;

require_once __DIR__ . '/vendor/autoload.php';

class Bot
{
  private $listeners;
  public $data;
  private $lineBot;

  public function __construct()
  {
    $httpClient = new CurlHTTPClient(getenv('CHANNEL_ACCESS_TOKEN'));
    $args = ['channelSecret' => getenv('CHANNEL_SECRET')];
    $this->lineBot = new LINEBot($httpClient, $args);
    $this->listeners = [];
    $this->data = json_decode(file_get_contents(__DIR__ . '/umaimon.json'), true);
  }

  private function parseEvent()
  {
    $body = file_get_contents('php://input');
    $signature = $_SERVER['HTTP_' . HTTPHeader::LINE_SIGNATURE];
    return $this->lineBot->parseEventRequest($body, $signature);
  }

  public function addListener(callable $listener)
  {
    $this->listeners[] = $listener;
  }

  public function execute()
  {
    foreach ($this->parseEvent() as $event) {
      foreach ($this->listeners as $listener) {
        if ($listener($event)) {
          break;
        }
      }
    }
  }

  public function replyMessage($replyToken, MessageBuilder $messageBuilder)
  {
    return $this->lineBot->replyMessage($replyToken, $messageBuilder);
  }
}