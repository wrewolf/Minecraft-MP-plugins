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
1.0
  unchest command added

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
      $this->api->console->register("unchest", "Release chest protect you. Stai on chest and run", array($this, "commandH"));
      $this->api->addHandler("player.block.break", array($this, "handle"), 7);
      $this->api->addHandler("player.block.touch", array($this, "handle"), 7);
    }

    public function commandH($cmd, $params, $issuer, $alias)
    {
      $output = "";
      switch ($cmd) {
        case "unchest":
          $x = round($issuer->entity->x - 0.5);
          $y = round($issuer->entity->y - 1);
          $z = round($issuer->entity->z - 0.5);

          unset($this->config[$x][$y][$z]);
          $this->api->plugin->writeYAML($this->path . "config.yml", $this->config);
          break;
        case "lschest":

          foreach ($this->config as $x => $xs) {
            foreach ($xs as $y => $ys) {
              foreach ($ys as $z => $zs) {
                $output .= "($x, $y, $z) : $zs";
              }
            }
          }
          break;
      }
      return $output;
    }

    public function __destruct()
    {
    }

    function debug_print($str)
    {
      //console($str);
    }

    public function handle($data, $event)
    {
      switch ($event) {
        case "player.block.touch":
          $this->debug_print("touch");
          $tile = $this->api->tile->get(new Position($data['target']->x, $data['target']->y, $data['target']->z, $data['target']->level));
          if ($tile === false) break;
          $class        = $tile->class;
          $this->config = $this->api->plugin->readYAML($this->path . "config.yml");
          if ($class == TILE_CHEST) {
            $this->debug_print("chest");
            if (isset($this->config[$data['target']->x][$data['target']->y][$data['target']->z])) {
              $this->debug_print("config est'");
              if ($this->config[$data['target']->x][$data['target']->y][$data['target']->z] !== $data['player']->username) {
                $this->api->chat->sendTo(false, "This chest is protected by {$this->config[$data['target']->x][$data['target']->y][$data['target']->z]}.", $data['player']->username);
                return false;
              } else {
                if ($data['item'] instanceof StickItem) {
                  $this->debug_print("stick reset");
                  unset($this->config[$data['target']->x][$data['target']->y][$data['target']->z]);
                  $this->api->plugin->writeYAML($this->path . "config.yml", $this->config);
                  $this->api->chat->sendTo(false, "Chest released", $data['player']->username);
                  return false;
                }
              }
            } else {
              if ($data['item'] instanceof StickItem) {
                $this->debug_print("stick set");
                $this->config[$data['target']->x][$data['target']->y][$data['target']->z] = $data['player']->username;
                $this->debug_print(print_r($this->config, true));
                $this->api->plugin->writeYAML($this->path . "config.yml", $this->config);
                $this->api->chat->sendTo(false, "Chest protected", $data['player']->username);
                return false;
              }
            }
          }
          break;
        case "player.block.break":
          if (isset($this->config[$data['target']->x][$data['target']->y][$data['target']->z]))
            unset($this->config[$data['target']->x][$data['target']->y][$data['target']->z]);
          break;
      }
    }
  }
