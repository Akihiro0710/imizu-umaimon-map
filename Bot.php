<?php

use LINE\LINEBot;
use LINE\LINEBot\Constant\HTTPHeader;
use LINE\LINEBot\HTTPClient\CurlHTTPClient;

require_once __DIR__ . '/vendor/autoload.php';

class Bot extends LINEBot
{
  private $listeners;

  public function __construct()
  {
    $httpClient = new CurlHTTPClient(getenv('CHANNEL_ACCESS_TOKEN'));
    $args = ['channelSecret' => getenv('CHANNEL_SECRET')];
    $this->listeners = [];
    parent::__construct($httpClient, $args);
  }

  public function parseEvent()
  {
    $body = file_get_contents('php://input');
    $signature = $_SERVER['HTTP_' . HTTPHeader::LINE_SIGNATURE];
    return parent::parseEventRequest($body, $signature);
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
}