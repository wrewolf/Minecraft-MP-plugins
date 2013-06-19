<?php
  /*
__PocketMine Plugin__
name=SetHome
description=SetHome
version=0.5
author=WreWolf
class=SetHome
apiversion=9
*/

  class SetHome implements Plugin
  {
    private $api, $server;

    public function __construct(ServerAPI $api, $server = false)
    {
      $this->api = $api;
      $this->server = $server;
    }

    public function init()
    {
      $this->api->console->register("sethome", "/sethome Set user home", array($this, "commandH"));
      $this->api->ban->cmdWhitelist("sethome");
    }

    public function __destruct()
    {
    }

    public function commandH($cmd, $params, $issuer, $alias)
    {
      $output = "";
      if ($issuer instanceof Player) {
        $spawn = new Position($issuer->entity->x, $issuer->entity->y, $issuer->entity->z, $issuer->entity->level);
	$issuer->setSpawn($spawn);
	$x = round($issuer->entity->x - 0.5);
        $y = round($issuer->entity->y);
        $z = round($issuer->entity->z - 0.5);
        $output .= "Spawnpoint set correctly! (" . $x . ", " . $y . ", " . $z . ")\n";
      } else {
        $output .= "Please run this command on the game.\n";
      }
      return $output;
    }
  }