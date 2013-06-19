<?php
  /*
__PocketMine Plugin__
name=GetC
description=GetC
version=0.5
author=WreWolf
class=GetC
apiversion=9
*/

  class GetC implements Plugin
  {
    private $api;

    public function __construct(ServerAPI $api, $server = false)
    {
      $this->api = $api;
    }

    public function init()
    {
      $this->api->console->register("getc", "/getc Get user coordinate", array($this, "commandH"));
      $this->api->ban->cmdWhitelist("getc");
    }

    public function __destruct()
    {
    }

    public function commandH($cmd, $params, $issuer, $alias)
    {
      $output = "";
      if ($issuer instanceof Player) {
        $x = round($issuer->entity->x - 0.5);
        $y = round($issuer->entity->y);
        $z = round($issuer->entity->z - 0.5);
        $output .= "Player position (" . $x . ", " . $y . ", " . $z . ")\n";
      } else {
        $output .= "Please run this command on the game.\n";
      }
      return $output;
    }
  }
