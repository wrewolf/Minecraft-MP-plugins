<?php
  /*
__PocketMine Plugin__
name=settime
description=settime
version=1
author=WreWolf
class=settime
apiversion=9
*/

  class settime implements Plugin
  {
    private $api;

    public function __construct(ServerAPI $api, $server = false)
    {
      date_default_timezone_set ("Europe/Moscow");
      $this->api = $api;
    }

    public function init()
    {
    }

    public function __destruct()
    {
    }
  }
