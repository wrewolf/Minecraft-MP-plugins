<?php

  /*
  __PocketMine Plugin__
  name=Portal
  description=
  version=0.1
  author=WreWolf
  class=PortalW
  apiversion=9
  */


  class PortalW implements Plugin
  {
    private $api;

    public function __construct(ServerAPI $api, $server = false)
    {
      $this->api = $api;
    }

    public function init()
    {
      $this->api->addHandler("tile.update", array($this, "eventHandler"));
      $this->api->addHandler("player.block.touch", array($this, "eventHandler"));
    }

    public function __destruct()
    {

    }

    public function handle($data, $event)
    {

    }

    public function eventHandler(&$data, $event)
    {
      switch ($event) {
        case "tile.update":
          if ($data->class === TILE_SIGN) {
            if ($data->data["Text1"] != "w:" and $data->data["Text3"] != "tp")
              return;
            $lvl = $data->data["Text2"];
            if ($this->api->level->loadLevel($lvl) === false) {
              $this->api->chat->sendTo(false, "Mir $lvl ne sushestvuet", $data->data['creator']);
              break;
            }
            $this->api->chat->broadcast("Portal to " . $data->data["Text1"] . $data->data["Text2"] . " created");
          }
          break;
        case "player.block.touch":
          $tile = $this->api->tile->get(new Position($data['target']->x, $data['target']->y, $data['target']->z, $data['target']->level));
          if ($tile === false) break;
          $class = $tile->class;
          switch ($class) {
            case TILE_SIGN:
              switch ($data['type']) {
                case "place":
                  console("touch sign place " . $tile->data['Text1'] . $tile->data['Text2']);
                  if ($tile->data['Text1'] == "w:" and $tile->data['Text3'] == "tp") {
                    $mapname = $tile->data['Text2'];
                    $lvlname = $tile->data['Text1'] . $mapname;
                    $username=$data['player']->username;
                    console("teleport('$lvlname', $username)");
                    $this->api->level->loadLevel($mapname);
                    $this->api->player->teleport($lvlname, $username);
                  }
                  break;
              }
              break;
          }
          break;
      }

    }

    function debug_print($str)
    {
      console($str);
    }
  }


