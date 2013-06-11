<?php

  /*
  __PocketMine Plugin__
  name=mobTest
  description=Create some NPCs!
  version=1.1
  author=zhuowei
  class=MobTest
  apiversion=7
  */

  /*
  Small Changelog
  ===============

  1.0: Initial release

  1.1: NPCs now chase you

  */


  class MobTest implements Plugin
  {

    private $api, $npclist, $config, $path, $ticksuntilupdate;

    public function __construct(ServerAPI $api, $server = false)
    {
      $this->api              = $api;
      $this->npclist          = array();
      $this->ticksuntilupdate = 0;
    }

    public function init()
    {
      $this->path = $this->api->plugin->configPath($this);
      $this->api->console->register("spawnmob", "Add an Mob. /spawnmob [name] [player location] OR /spawnmob [name] <x> <y> <z> <world>", array($this, "command"));
      $this->api->console->register("rmmob", "Remove an NPC. /rmnpc [name]", array($this, "rmcommand"));
      $this->api->schedule($this->ticksuntilupdate, array($this, "tickHandler"), array(), true, "server.schedule");
      $this->config = new Config($this->path . "config.yml", CONFIG_YAML, array(
        "mobs" => array(),
      ));
      $this->spawnAllNpcs();

    }

    public function spawnAllNpcs()
    {
      $npcconflist = $this->config->get("mobs");
      if (!is_array($npcconflist)) {
        $this->config->set("npcs", array());
        return;
      }
      foreach (array_keys($npcconflist) as $pname) {
        $p   = $npcconflist[$pname];
        $pos = new Position($p["Pos"][0], $p["Pos"][1], $p["Pos"][2], $this->api->level->getDefault());
        createNpc($pname, $pos);
      }
    }

    public function command($cmd, $params, $issuer, $alias)
    {
      console($cmd . " " . print_r($params, true));
      $npcname  = $params[0];
      $location = $this->api->level->getDefault()->getSpawn();
      if (count($params) <= 2) {
        $locationPlayer = $this->api->player->get($params[1]);
        if ($locationPlayer instanceof Player and ($locationPlayer->entity instanceof Entity)) {
          $location = $locationPlayer->entity;
        }
      } else {
        $locationX = $params[1];
        $locationY = $params[2];
        $locationZ = $params[3];
        if (count($params) > 4) {
          $locationWorld = $params[4];
        } else {
          $locationWorld = $this->api->level->getDefault();
        }
        $location = new Position($locationX, $locationY, $locationZ, $locationWorld);
      }
      $this->createMob($npcname, $location);
      return "Created Mob at " . $location;
    }

    public function rmcommand($cmd, $params, $issuer, $alias)
    {
      $npcname = $params[0];
      $this->removeNpc($npcname);
      return "Removed NPC " . $npcname;
    }


    public function createMob($npcname, $location)
    {
      $npcplayer             = new Player("0", "127.0.0.1", 0, 0); //all NPC related packets are fired at localhost
      $npcplayer->spawned    = true;
      $playerClassReflection = new ReflectionClass(get_class($npcplayer));
      $usernameField         = $playerClassReflection->getProperty("username");
      $usernameField->setAccessible(true);
      $usernameField->setValue($npcplayer, $npcname);
      $iusernameField = $playerClassReflection->getProperty("iusername");
      $iusernameField->setAccessible(true);
      $iusernameField->setValue($npcplayer, strtolower($npcname));
      $timeoutField = $playerClassReflection->getProperty("timeout");
      $timeoutField->setAccessible(true);
      $timeoutField->setValue($npcplayer, PHP_INT_MAX - 0xff);

      /*
    define("MOB_CHICKEN", 10);
    define("MOB_COW", 11);
    define("MOB_PIG", 12);
    define("MOB_SHEEP", 13);
    	    
    define("MOB_ZOMBIE", 32);
    define("MOB_CREEPER", 33);
    define("MOB_SKELETON", 34);
    define("MOB_SPIDER", 35);
    define("MOB_PIGMAN", 36);
      */
      $npcname = strtolower(trim($npcname));
      switch ($npcname) {
        case "chicken":
          $type = 10;
          break;
        case "cow":
          $type = 11;
          break;
        case "pig":
          $type = 12;
          break;
        case "sheep":
          $type = 13;
          break;
        case "zombie":
          $type = 32;
          break;
        case "creeper":
          $type = 33;
          break;
        case "skeleton":
          $type = 34;
          break;
        case "spider":
          $type = 35;
          break;
        case "pigman":
          $type = 36;
          break;
        case "rdz":
          $type = rand(32, 36);
          break;
        case "rdm":
          $type = rand(10, 13);
          break;
        default:
          $type = 11;
      }
      console("npcname=$npcname and type=$type");
      $entityit = $this->api->entity->add($this->api->level->getDefault(), ENTITY_MOB, $type, array(
        "x"      => $location->x,
        "y"      => $location->y,
        "z"      => $location->z,
        "Health" => 10,
        "player" => $npcplayer
      ));
      $entityit->setName($npcname);
      $this->api->entity->spawnToAll($entityit, $this->api->level->getDefault());
      $npcplayer->entity = $entityit;
      array_push($this->npclist, $npcplayer);
      $npcconf                             = array(
        "Pos"          => array(
          0 => $location->x,
          1 => $location->y,
          2 => $location->z,
        ),
        "mobile"       => true,
        "name"         => $npcname,
        "targetupdate" => 0
      );
      $this->config->get("mobs")[$npcname] = $npcconf;
      $this->config->save();
      $npcplayer->data["npcconf"] = $npcconf;
    }

    public function removeNpc($npcname)
    {
      foreach (array_keys($this->npclist) as $pk) {
        $p = $this->npclist[$pk];
        if ($p->entity->name === $npcname) {
          $this->api->entity->remove($p->entity->eid);
          unset($this->npclist[$pk]);
          break;
        }
      }
      unset($this->config->get("mobs")[$npcname]);
      $this->config->save();
    }

    public function tickHandler($data, $event)
    {
      $this->ticksuntilupdate = $this->ticksuntilupdate - 1;
      //$this->ticksuntilretarget = $this->ticksuntilretarget - 1;
      $checkupdate = false;
      //$targetupdate             = false;
      if ($this->ticksuntilupdate <= 0) {
        $this->ticksuntilupdate = 10;
        $checkupdate            = true;
      }
      /*      if ($this->ticksuntilretarget <= 0) {
              $this->ticksuntilretarget = 100;
              $targetupdate             = true;
            }*/
      foreach ($this->npclist as $p) {
        if ($p->entity->dead) {
          $p->entity->fire = 0;
          $p->entity->air  = 300;
          $p->entity->setHealth(20, "respawn");
          $p->entity->updateMetadata();
          $this->api->entity->spawnToAll($p->entity, $p->level);
        }
        if ($checkupdate) {
          //if (isset($p->data["target"]) and $p->data["target"] instanceof Entity) {
          $p->data["npcconf"]["targetupdate"]--;
          if (!isset($p->data["target"]) or $p->data["npcconf"]["targetupdate"] <= 0) {
            $p->data["npcconf"]["targetupdate"] = rand(40, 80);
            $p->data["target"]                  = array("x" => rand(0, 255), "y" => rand(50, 80), "z" => rand(0, 255));
            console("new target " . $p->data["npcconf"]["name"] . " timeout=" . $p->data["npcconf"]["targetupdate"] . " dest ( " . $p->data["target"]["x"] . " , " . $p->data["target"]["y"] . " , " . $p->data["target"]["z"] . " ) ");
          }
          $target = $p->data["target"];
          //if ($target->closed) {
          //  unset($p->data["target"]);
          //} else {
          $xdiff    = $target["x"] - $p->entity->x;
          $ydiff    = $target["y"] - $p->entity->y;
          $zdiff    = $target["z"] - $p->entity->z;
          $distaway = pow($xdiff, 2) + pow($ydiff, 2) + pow($zdiff, 2);
          $angle    = atan2($zdiff, $xdiff) + rand(-M_PI/6,M_PI/6) ;
          if ($p->data["npcconf"]["mobile"]) {
            if ($distaway > 5 and rand(0, 100) > 25) {
              $speedX            = cos($angle) * rand(10, 14) / 100;
              $speedZ            = sin($angle) * rand(10, 14) / 100;
              $p->entity->speedX = $speedX;
              $p->entity->speedZ = $speedZ;
            } else {
              $p->entity->speedX = 0;
              $p->entity->speedZ = 0;
            }
          } else {
            $p->entity->speedX = 0;
            $p->entity->speedZ = 0;
          }
          $p->entity->yaw = (($angle * 180) / M_PI) - rand(40, 14);
          $this->fireMoveEvent($p->entity);

          //}
          //}

          /*          $mindist   = 4500;
                    $minplayer = null;
                    foreach ($this->api->player->getAll($p->entity->level) as $otherp) {
                      if ($otherp === $p) continue;
                      if (!$otherp->connected or !$otherp->spawned) continue;
                      $distaway = pow($p->entity->x - $otherp->entity->x, 2) + pow($p->entity->y - $otherp->y, 2) + pow($p->entity->z - $otherp->entity->z, 2);
                      console("rastoyanie " . $p->data{"npcconf"}["name"] . " - " . $otherp->username . " = " . $distaway);
                      if (($minplayer == null || $distaway < $mindist) && $distaway<6000 ) {
                        $mindist   = $distaway;
                        $minplayer = $otherp;
                      }
                    }
                    if (!($minplayer instanceof Player) or $minplayer == null) {
                      $p->data["target"] = null;
                      console("min rastoyanie " . $p->data["npcconf"]["name"] . " - " . $otherp->username . " = " . $distaway);
                    } else {
                      $p->data["target"] = $minplayer->entity;
                    }*/
        }
        //TODO: more physics on the players. Attacking
      }
    }


    public function fireMoveEvent($entity)
    {
      if ($entity->speedX != 0) {
        $entity->x += $entity->speedX * 5;
      }
      if ($entity->speedY != 0) {
        $entity->y += $entity->speedY * 5;
      }
      if ($entity->speedZ != 0) {
        $entity->z += $entity->speedZ * 5;
      }
      if (($entity->last[0] != $entity->x or $entity->last[1] != $entity->y or $entity->last[2] != $entity->z or $entity->last[3] != $entity->yaw or $entity->last[4] != $entity->pitch)) {
        if ($this->api->handle("entity.move", $entity) === false) {
          $entity->setPosition($entity->last[0], $entity->last[1], $entity->last[2], $entity->last[3], $entity->last[4]);
        }
        $entity->updateLast();
      }
    }


    public function __destruct()
    {

    }


  }
