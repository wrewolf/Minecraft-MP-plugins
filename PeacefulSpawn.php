<?php

  /*
  __PocketMine Plugin__
  name=PeacefulSpawn
  description=Players can't harm eachother at spawn
  version=1.0
  author=wiezz
  class=PeacefulSpawn
  apiversion=9
  */


  class PeacefulSpawn implements Plugin
  {
    private $api;
    private $server;

    public function __construct(ServerAPI $api, $server = false)
    {
      $this->api    = $api;
      $this->server = ServerAPI::request();
    }

    public function init()
    {
      $this->api->addHandler("player.interact", array($this, "PlayerInteractHandler"));
    }

    public function PlayerInteractHandler($data, $event)
    {
      if (!isset($data["target"])) return false;
      $target = $this->api->entity->get($data["target"]);
      if (!isset($target)) return false;
      $t        = new Vector3($target->x, $target->y, $target->z);
      $s        = new Vector3($this->server->spawn->x, $this->server->spawn->y, $this->server->spawn->z);
      $distance = $t->distance($s);
      if ($distance <= $this->server->api->getProperty("spawn-protection")) {
        return false;
      }
      return true;
    }

    public function __destruct()
    {

    }

  }