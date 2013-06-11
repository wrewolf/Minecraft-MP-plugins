<?php
  /*
__PocketMine Plugin__
name=Helper
description=Helper
version=0.1
author=WreWolf
class=Helper
apiversion=7
*/
  /*

Small Changelog
===============

0.1:

*/
  class Helper implements Plugin
  {
    private $api, $interval;

    public function __construct(ServerAPI $api, $server = false)
    {
      $this->api      = $api;
      $this->interval = array();
    }

    public function init()
    {
      $this->api->addHandler('player.block.touch', array($this, 'handle'), 9);
      $this->api->event('server.schedule', array($this, 'handle'));
    }

    public function __destruct()
    {
    }

    public function handle(&$data, $event)
    {
      switch ($event) {
        case "player.block.touch":
          $level = $this->api->level->get("wrewolf");
          $sign  = $this->api->tile->get(new Position(87, 71, 119, $level));
          if (($sign instanceof Tile) && $sign->class === TILE_SIGN) {
        	$sign->data['Text1']="Text1";
        	$sign->data['Text2']="Text2";
        	$sign->data['Text3']="Text3";
        	$sign->data['Text4']="Text4";
          }
          break;
        case "server.schedule":
         $level = $this->api->level->get("wrewolf");
          $sign  = $this->api->tile->get(new Position(87, 71, 119, $level));
          if (($sign instanceof Tile) && $sign->class === TILE_SIGN) {
        	$sign->data['Text1']="Text1";
        	$sign->data['Text2']="Text2";
        	$sign->data['Text3']="Text3";
        	$sign->data['Text4']="Text4";
          }
          break;
      }
    }
  }
