<?php
  /*
__PocketMine Plugin__
name=ChestProtect
description=ChestProtect
version=0.1
author=WreWolf
class=ChestProtect
apiversion=9
*/
  /*

Small Changelog
===============

0.1:

*/
  class ChestProtect implements Plugin
  {
    private $api, $path, $config;

    public function __construct(ServerAPI $api, $server = false)
    {
      $this->api = $api;
    }

    public function init()
    {
      $this->path   = $this->api->plugin->createConfig($this, array(array(array())));
      $this->config = $this->api->plugin->readYAML($this->path . "config.yml");
      $this->api->addHandler("player.block.place", array($this, "handle"), 7);
      $this->api->addHandler("player.block.touch", array($this, "handle"), 7);
    }

    public function __destruct()
    {
    }

    public function handle($data, $event)
    {
      switch ($event) {
        case "player.block.touch":

          $tile = $this->api->tile->get(new Position($data['target']->x, $data['target']->y, $data['target']->z, $data['target']->level));
          if ($tile === false) break;
          $class        = $tile->class;
          $this->config = $this->api->plugin->readYAML($this->path . "config.yml");

          if ($class == TILE_CHEST) {
            console("chest");
            if (isset($this->config[$data['target']->x][$data['target']->y][$data['target']->z])) {
              console("config est'");
              if ($this->config[$data['target']->x][$data['target']->y][$data['target']->z] !== $data['player']->username) {
                $this->api->chat->sendTo(false, "This chest is protected by {$this->config[$data['target']->x][$data['target']->y][$data['target']->z]}.", $data['player']->username);
                return false;
              } else {

                if ($data['item'] instanceof StickItem) {
                  console("stick reset");
                  unset($this->config[$data['target']->x][$data['target']->y][$data['target']->z]);
                  $this->api->plugin->writeYAML($this->path . "config.yml", $this->config);
                  $this->api->chat->sendTo(false, "Chest released", $data['player']->username);
                  return false;
                }
              }
            } else {
              if ($data['item'] instanceof StickItem) {
                console("stick set");
                $this->config[$data['target']->x][$data['target']->y][$data['target']->z] = $data['player']->username;
                console(print_r($this->config, true));
                $this->api->plugin->writeYAML($this->path . "config.yml", $this->config);
                $this->api->chat->sendTo(false, "Chest protected", $data['player']->username);
                return false;
              }
            }
          }
          break;
      }
    }
  }
