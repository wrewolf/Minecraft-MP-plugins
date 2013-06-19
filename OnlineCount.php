<?php
  /*
__PocketMine Plugin__
name=OnlineCount
description=OnlineCount
version=0.5
author=WreWolf
class=OnlineCount
apiversion=9
*/

  class OnlineCount implements Plugin
  {
    private $api, $path, $config;

    public function __construct(ServerAPI $api, $server = false)
    {
      $this->api = $api;
    }

    public function init()
    {
      $this->createConfig();
      $this->api->console->register('online', 'online on the server.', array($this, 'commandH'));
      $this->api->console->register('tstonline', 'online on the server.', array($this, 'commandH'));
      //this->api->schedule(5000, array($this, "tickHandler"), array(), true, "server.schedule");
    }

    public function tickHandler($data, $event)
    {
      $today = date("Y-m-d H:i:s");
      $this->api->plugin->writeYAML($this->path,$today . " " . $this->config["count"]);
    }


    public function commandH($cmd, $params, $issuer, $alias)
    {
      $output = "";
      switch ($cmd) {
        case 'online':
          $players=$this->api->player->getAll();
          $output="Online " . count($players);
          break;
        case 'tstonline':

          break;
      }
      return $output;
    }

    public function createConfig()
    {
      $default      = array("count" => 0, "list" => array());
      $this->path   = $this->api->plugin->createConfig($this, $default);
      $this->config = $this->api->plugin->readYAML($this->path . "config.yml");
    }

    public function __destruct()
    {
    }
  }
