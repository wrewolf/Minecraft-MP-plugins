<?php
  /*
__PocketMine Plugin__
name=SignConsole
description=Console access by sign
version=0.1
author=WreWolf
class=SignConsole
apiversion=7
*/
  /*

Small Changelog
===============

0.1:
- SignConsole fork SignConsole Omattyao v2.0.4

0.2
- Fix API

*/
  class SignConsole implements Plugin
  {
    private $api, $interval;

    public function __construct(ServerAPI $api, $server = false)
    {
      $this->api      = $api;
      $this->interval = array();
    }

    public function init()
    {
      $this->api->addHandler('tile.update', array($this, 'handle'), 9);
      $this->api->addHandler('player.block.touch', array($this, 'handle'), 9);
    }

    public function __destruct()
    {
    }

    public function handle(&$data, $event)
    {
      switch ($event) {
        case "tile.update":
          if ($data->class === TILE_SIGN) {
            $strings = $data->data['Text1'] . $data->data['Text2'] . $data->data['Text3'] . $data->data['Text4'];
            if (isset($strings))
              if (strpos($strings, '//') !== false) {
                $username = $data->data['creator'];
                if (!$this->checkInterval($username)) break;
                preg_match("/^\/\/(.+[^\\s])/", $strings, $line);
                $line   = $line[1];
                $issuer = $this->api->player->get($username);
                $this->api->console->run($line, $issuer, false);
                $this->breakSign($data, $issuer);
                $this->setInterval($username);
                return false;
              }
          }
          break;
        case "player.block.touch":
          if ($data['type'] !== "place") {
            break;
          }
          if ($data['item']->getID() !== 323) {
            break;
          }
          $level = $this->api->level->getDefault();
          $sign  = $this->api->tileentity->get(new Position($data['target']->x, $data['target']->y, $data['target']->z, $level));
          if (($sign[0] instanceof TileEntity) && $sign[0]->class === TILE_SIGN) {
            $sign    = $sign[0];
            $strings = $sign->data['Text1'] . $sign->data['Text2'] . $sign->data['Text3'] . $sign->data['Text4'];
              $username = $data['player']->username;
              if (!$this->checkInterval($username)) break;
              preg_match("/^\/\/(.+[^\\s])/", $strings, $line);
              $line   = $line[1];
              $issuer = $this->api->player->get($username);
              $this->api->console->run($line, $data['player'], false);
              $this->setInterval($username);
              return false;
          }
          break;
      }
    }

    public function checkInterval($player)
    {
      if (time() < $this->interval[$player]) {
        return false;
      } else {
        return true;
      }
    }

    public function setInterval($player)
    {
      $time                    = time() + 2;
      $this->interval[$player] = $time;
    }

    public function breakSign($sign, $player)
    {
      $level    = $this->api->level->getDefault();
      $position = new Position($sign->x, $sign->y, $sign->z,$level);
      $block    = $level->getBlock($position);
      $id=$block->getID();
      $b=new AirBlock();
      if ($id == 63 || $id == 68) {
        $level->setBlock($position, $b);
      }
      if ($player->gamemode !== CREATIVE) {
      $s=new SignItem();
        $this->api->entity->drop($position, $s);
      }
      $this->api->tileentity->remove($sign->id);
    }
  }
