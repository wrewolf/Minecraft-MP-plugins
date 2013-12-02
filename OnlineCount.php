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
    private $api, $path, $config, $server, $db;

    public function __construct(ServerAPI $api, $server = false)
    {
      $this->api    = $api;
      $this->server = ServerAPI::request();
    }

    public function init()
    {
      $this->path = $this->api->plugin->configPath($this);
      @mkdir($this->path);
      $this->api->schedule(100, array($this, "tickHandler"), array(), true, "server.schedule");

      if ($this->db = new SQLite3($this->path."main.db")) {
        // first let the engine check table, and create it eventualy
        $q = @$this->db->query('CREATE TABLE IF NOT EXISTS main (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, "timestamp" NOT NULL DEFAULT CURRENT_TIMESTAMP, tps real, online int);');
      }
    }

    public function tickHandler($data, $event)
    {
      $data             = array();
      $data['time']     = date("Y-m-d H:i:s");
      $data['online']   = "Online: " . count($this->api->player->getAll());
      $info             = $this->server->debugInfo();
      $data['tps']      = "TPS: " . $info["tps"];
      $data['mem']      = "Memory: " . $info["memory_usage"];
      $data['peakmem']  = "Peak: " . $info["memory_peak_usage"];
      $data['entities'] = "Entities: " . $info["entities"];
      $data['players']  = "Players: " . $info["players"];
      $data['events']   = "Events: " . $info["events"];
      $data['handlers'] = "Handlers: " . $info["handlers"];
      $data['actions']  = "Actions: " . $info["actions"];
      $data['garbage']  = "Garbage cycles: " . $info["garbage"];
      $data['users']    = array();
      foreach ($this->server->clients as $c) {
        $data['users'][] = array('name' => $c->username, 'x' => $c->entity->x, 'y' => $c->entity->y, 'z' => $c->entity->z, 'level' => $c->level->getName());
      }

      $ops                 = new Config(DATA_PATH . "ops.txt", CONFIG_LIST); //Open list of OPs
      $data['ops'][]       = $ops->getAll(true);
      $banned              = new Config(DATA_PATH . "banned.txt", CONFIG_LIST); //Open Banned Usernames list file
      $data['banned'][]    = $banned->getAll(true);
      $bannedIPs           = new Config(DATA_PATH . "banned-ips.txt", CONFIG_LIST); //Open Banned IPs list file
      $data['bannedIPs'][] = $bannedIPs->getAll(true);

      //console(print_r(json_encode($data), true));
      file_put_contents($this->path . "counter.txt", json_encode($data));
      preg_match_all('!\d+\.*\d*!', $info['tps'], $matches);
      $tps = $matches[0][0];
      $this->db->query("INSERT INTO main (tps,online) VALUES ('$tps','{$info["players"]}')");
    }

    public function __destruct()
    {
    }
  }
