<?php
  /*
__PocketMine Plugin__
name=SimpleMap
description=SimpleMap
version=0.5
author=wrewolf
class=SimpleMap
apiversion=9
*/

  class SimpleMap implements Plugin
  {
    private $api, $tiks, $path;

    public function __construct(ServerAPI $api, $server = false)
    {
      $this->api = $api;
    }

    public function init()
    {
      $this->tiks = 0;

      $this->api->console->register('makemap', 'online on the server.', array($this, 'commandH'));
console("Array =" . print_r(array(AIR,TORCH,SIGN_POST,WALL_SIGN),true) );
    }

    public function commandH($cmd, $params, $issuer, $alias)
    {
      $output = "";
      $this->path = $this->api->plugin->createConfig($this, array(array()));
      console("Map to update");
      $map   = array(array());
      $level = $this->api->level->getDefault();
      for ($x = 0; $x <= 256; $x++) {
        console($x);
        for ($z = 0; $z <= 256; $z++) {
          //console("($x, $z)");

          for ($y = 127; $y >= 0; $y--) {
            $pos   = new Vector3($x, $y, $z);
            $id = (int)$level->getBlock($pos)->getID();

            if (!in_array($id,array(AIR,TORCH,SIGN_POST,WALL_SIGN))) {
              //console("$x,$z,$y:$id");
              $map[$x][$z] = $id;
              break;
            }
          }
        }
      }
      console("Map write update");
      $this->api->plugin->writeYAML($this->path . "config.yml", $map);
      $output .= "Map updated";
      return $output;
    }


    public function __destruct()
    {
    }
  }
