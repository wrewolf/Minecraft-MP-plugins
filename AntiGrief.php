<?php
  /*
__PocketMine Plugin__
name=AntiGrief
description=AntiGrief
version=0.1
author=DemonKingz
class=AntiGrief
apiversion=9
*/
  class AntiGrief implements Plugin
  {
    private $api;

    public function __construct(ServerAPI $api, $server = false)
    {
      $this->api = $api;
    }

    public function init()
    {
      $this->api->addHandler("player.block.place", array($this, "playerBlockPlaceHandle"));
    }

    public function __destruct()
    {
    }

    public function playerBlockPlaceHandle(&$data)
    {
      if (
        $data['item']->getID() === 4 or
        $data['item']->getID() === 98 or
        $data['item']->getID() === 48 or
        $data['item']->getID() === 5 or
        $data['item']->getID() === 45 or
        $data['item']->getID() === 1 or
        $data['item']->getID() === 3 or
        $data['item']->getID() === 2 or
        $data['item']->getID() === 82 or
        $data['item']->getID() === 24 or
        $data['item']->getID() === 12 or
        $data['item']->getID() === 13 or
        $data['item']->getID() === 17 or
        $data['item']->getID() === 112 or
        $data['item']->getID() === 87 or
        $data['item']->getID() === 67 or
        $data['item']->getID() === 53 or
        $data['item']->getID() === 108 or
        $data['item']->getID() === 128 or
        $data['item']->getID() === 109 or
        $data['item']->getID() === 114 or
        $data['item']->getID() === 156 or
        $data['item']->getID() === 44 or
        $data['item']->getID() === 155 or
        $data['item']->getID() === 16 or
        $data['item']->getID() === 15 or
        $data['item']->getID() === 14 or
        $data['item']->getID() === 56 or
        $data['item']->getID() === 21 or
        $data['item']->getID() === 73 or
        $data['item']->getID() === 41 or
        $data['item']->getID() === 42 or
        $data['item']->getID() === 57 or
        $data['item']->getID() === 22 or
        $data['item']->getID() === 49 or
        $data['item']->getID() === 80 or
        $data['item']->getID() === 20 or
        $data['item']->getID() === 89 or
        $data['item']->getID() === 247 or
        $data['item']->getID() === 35 or
        $data['item']->getID() === 65 or
        $data['item']->getID() === 50 or
        $data['item']->getID() === 102 or
        $data['item']->getID() === 324 or
        $data['item']->getID() === 96 or
        $data['item']->getID() === 85 or
        $data['item']->getID() === 107 or
        $data['item']->getID() === 355 or
        $data['item']->getID() === 47 or
        $data['item']->getID() === 321 or
        $data['item']->getID() === 58 or
        $data['item']->getID() === 245 or
        $data['item']->getID() === 54 or
        $data['item']->getID() === 61 or
        $data['item']->getID() === 46 or
        $data['item']->getID() === 37 or
        $data['item']->getID() === 38 or
        $data['item']->getID() === 39 or
        $data['item']->getID() === 40 or
        $data['item']->getID() === 81 or
        $data['item']->getID() === 103 or
        $data['item']->getID() === 338 or
        $data['item']->getID() === 6 or
        $data['item']->getID() === 18 or
        $data['item']->getID() === 295 or
        $data['item']->getID() === 362 or
        $data['item']->getID() === 351 or
        $data['item']->getID() === 292 or
        $data['item']->getID() === 267 or
        $data['item']->getID() === 261 or
        $data['item']->getID() === 323
      ) {
        return true;
      }
      return false;
    }
  }

