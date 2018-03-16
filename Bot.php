<?php

use LINE\LINEBot;
use LINE\LINEBot\Constant\HTTPHeader;
use LINE\LINEBot\HTTPClient\CurlHTTPClient;
use LINE\LINEBot\MessageBuilder;
use LINE\LINEBot\TemplateActionBuilder\PostbackTemplateActionBuilder;
use LINE\LINEBot\TemplateActionBuilder\UriTemplateActionBuilder;

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
    $response = $this->lineBot->replyMessage($replyToken, $messageBuilder);
    if (!$response->isSucceeded()) {
      error_log('Failed!' . $response->getHTTPStatus() . ' ' . $response->getRawBody());
    }
  }

  public function createShopDataParams($key)
  {
    $shop = $this->data[$key];
    $title = $shop['name'];
    $tel = $shop['tel'];
    $summary = $shop['summary'];
    if (mb_strlen($summary) > 60) {
      $summary = mb_substr($summary, 0, 59) . '…';
    }
    return [
        $title,
        $summary,
        "https://{$_SERVER["HTTP_HOST"]}/images/{$key}.jpg",
        [
            new UriTemplateActionBuilder($tel, 'tel:' . $tel),
            new PostbackTemplateActionBuilder('詳細を見る', $key)
        ]
    ];
  }
}