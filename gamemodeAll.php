<?php
  /*
__PocketMine Plugin__
name=gamemodeAll
description=gamemodeAll
version=0.5
author=WreWolf
class=gamemodeAll
apiversion=9
*/

  class gamemodeAll implements Plugin
  {
    private $api, $config, $path;

    public function __construct(ServerAPI $api, $server = false)
    {
      $this->api = $api;
    }

    public function init()
    {
      $this->api->console->register("gamemodea", "/gamemodea <mode>", array($this, "commandH"));
      $this->api->event('player.join', array($this, 'handle'));
    }

    public function __destruct()
    {
    }

    public function handle($data, $event)
    {
      $player = $this->api->player->get($data->username);
      $player->setGamemode($this->config);
      $output = 'Gamemode of ' . $player->username . ' changed to ' . $player->getGamemode() . '\n';

      return $output;
    }
    public function commandH($cmd, $params, $issuer, $alias)
    {
      console($params[0]);
      $output  = '';
      $players = $this->api->player->getAll();
      $gms     = array(
        "0"         => SURVIVAL,
        "survival"  => SURVIVAL,
        "s"         => SURVIVAL,
        "1"         => CREATIVE,
        "creative"  => CREATIVE,
        "c"         => CREATIVE,
        "2"         => ADVENTURE,
        "adventure" => ADVENTURE,
        "a"         => ADVENTURE,
        "3"         => VIEW,
        "view"      => VIEW,
        "viewer"    => VIEW,
        "spectator" => VIEW,
        "v"         => VIEW,
      );
      $this->config =$gms[strtolower($params[0])];
      $this->api->plugin->writeYAML($this->path . "config.yml", $this->config);
      $output .= "Default gamemode changed to  $this->config";
      $players=$this->api->player->getAll();
      foreach($players as $player)
      {
        $player->$player->setGamemode($this->config);
      }

      return $output;
    }



    public function createConfig()
    {
      $default      = SURVIVAL;
      $this->path   = $this->api->plugin->createConfig($this, $default);
      $this->config = $this->api->plugin->readYAML($this->path . "config.yml");
    }
  }
