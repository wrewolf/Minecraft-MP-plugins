<?php

  /*
__PocketMine Plugin__
name=OnlineCount
description=OnlineCount
version=0.5
author=WreWolf
class=OnlineCount
apiversion=12
*/

  class OnlineCount implements Plugin
  {
    private $api, $server,$mysqli;

    public function __construct(ServerAPI $api, $server = false)
    {
      $this->api    = $api;
      $this->server = ServerAPI::request();
    }

    public function init()
    {
      $this->api->schedule(100, array($this, "tickHandler"), array(), true, "server.schedule");
      //$this->api->schedule(10000, array($this, "cronHandler"), array(), true, "server.schedule");
      $this->mysqli = new mysqli('127.0.0.1', 'root', null);
      $this->mysqli->select_db("m1");
    }

    //public function cronHandler($data, $event)
    //{
//
    //}

    public function tickHandler($data, $event)
    {
      $data             = array();
      $data['time']     = date("Y-m-d H:i:s");
      $data['online']   = "Online: " . count($this->api->player->getAll());
      $info             = $this->server->debugInfo();
      $data['tps']      = $info["tps"];
      $data['mem']      = $info["memory_usage"];
      $data['peakmem']  = $info["memory_peak_usage"];
      $data['entities'] = $info["entities"];
      $data['players']  = $info["players"];
      $data['events']   = $info["events"];
      $data['handlers'] = $info["handlers"];
      $data['actions']  = $info["actions"];
      $data['garbage']  = $info["garbage"];

      $misc             = $this->mysqli->real_escape_string(json_encode($data));

      $playersA = array();
      $clients = $this->api->player->getAll();
      foreach ($clients as $c) {
        if ($c->username === "")
          continue;
          if($c instanceof Player)
            $playersA[] = array('name' => $c->username, 'x' => $c->entity->x, 'y' => $c->entity->y, 'z' => $c->entity->z, 'level' => $c->level->getName());
      }

      $online              = $this->mysqli->real_escape_string(json_encode($playersA));
      $ops                 = new Config(DATA_PATH . "ops.txt", CONFIG_LIST); //Open list of OPs
      $data['ops'][]       = $ops->getAll(true);
      $ops                 = $this->mysqli->real_escape_string(json_encode($data['ops']));
      $banned              = new Config(DATA_PATH . "banned.txt", CONFIG_LIST); //Open Banned Usernames list file
      $data['banned'][]    = $banned->getAll(true);
      $banned              = $this->mysqli->real_escape_string(json_encode($data['banned']));
      $bannedIPs           = new Config(DATA_PATH . "banned-ips.txt", CONFIG_LIST); //Open Banned IPs list file
      $data['bannedIPs'][] = $bannedIPs->getAll(true);
      $bannedips           = $this->mysqli->real_escape_string(json_encode($data['bannedIPs']));
      $misc_q              = "UPDATE misc SET ops='$ops', banned='$banned', bannedips='$bannedips', misc='$misc', online='$online' WHERE id=1;";

      preg_match_all('!\d+\.*\d*!', $info['tps'], $matches);
      $tps   = $matches[0][0];
      $time  = date('Y-m-d H:i:s');
      $query = "INSERT INTO main (tps,online,timestamp) VALUES ('$tps','{$info["players"]}','$time');" . $misc_q;

      foreach ($playersA as $key => $player) {
        $query .= "INSERT INTO online (user,cnt,level,x,y,z,timestamp) VALUES ('{$player['name']}',0,'{$player['level']}','{$player['x']}','{$player['y']}','{$player['z']}','$time') ON DUPLICATE KEY UPDATE cnt = cnt+1,level='{$player['level']}',x='{$player['x']}',y='{$player['y']}',z='{$player['z']}',timestamp='$time';";
      }

      $data = array('query' => $query);
      $this->api->handle("db.query.exec", $data);
    }

    public function __destruct()
    {
    }
  }
