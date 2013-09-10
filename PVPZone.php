<?php

  /*
  __PocketMine Plugin__
  name=PVPZone
  description=Players can damage only in PVP Zone
  version=0.1
  author=WreWolf
  class=PVPZone
  apiversion=9
  */


  class PVPZone implements Plugin
  {
    private $api, $path, $config;

    public function __construct(ServerAPI $api, $server = false)
    {
      $this->api    = $api;
      $this->server = ServerAPI::request();
      $this->path   = $this->api->plugin->createConfig($this, array());
      $this->config = $this->api->plugin->readYAML($this->path . "config.yml");
    }

    public function init()
    {
      $this->api->addHandler("player.interact", array($this, "eventHandler"));
      $this->api->console->register("setpvpzone", "Set pvp zone the area from console.", array($this, "commandH"));
    }

    public function eventHandler($data)
    {
      $target = $this->api->entity->get($data["target"]);
if(!isset($target))return;
      if ($this->config[$target->level]['min'][0] <= $target->x and $target->x <= $this->config[$target->level]['max'][0]) {
        if ($this->config[$target->level]['min'][1] <= $target->y and $target->y <= $this->config[$target->level]['max'][1]) {
          if ($this->config[$target->level]['min'][2] <= $target->z and $target->z <= $this->config[$target->level]['max'][2]) {
            return false;
          }
        }
      }
    }

    public function commandH($cmd, $params, $issuer, $alias)
    {
      $output = "";
      switch ($cmd) {
        case "setpvpzone":
          if ($issuer != "console") {
            $output .= "Only from console";
            break;
          }
          if (count($params) == 8) {
            $level = array_shift($params);
            $pos1  = array(array_shift($params), array_shift($params), array_shift($params));
            $pos2  = array(array_shift($params), array_shift($params), array_shift($params));
            $minX  = min($pos1[0], $pos2[0]);
            $maxX  = max($pos1[0], $pos2[0]);
            $minY  = min($pos1[1], $pos2[1]);
            $maxY  = max($pos1[1], $pos2[1]);
            $minZ  = min($pos1[2], $pos2[2]);
            $maxZ  = max($pos1[2], $pos2[2]);
            $max   = array($maxX, $maxY, $maxZ);
            $min   = array($minX, $minY, $minZ);

            $this->config[$level] = array("pvp" => true, "min" => $min, "max" => $max);
            $this->writeConfig($this->config);
            $output .= "set pvp zone this area ($minX, $minY, $minZ)-($maxX, $maxY, $maxZ) : $level\n";
          } else {
            $output .= "usage setpvpzone level x1 y1 z1 x y2 z2";
          }
          break;
      }
      return $output;
    }

    public function __destruct()
    {

    }

  }