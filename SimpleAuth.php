<?php

  /*
  __PocketMine Plugin__
  name=SimpleAuth
  description=Prevents people to impersonate an account, requering registration and login when connecting. modified by WreWolf sha1 password to back compatybility with ResidentManager
  version=0.2
  author=shoghicp
  class=SimpleAuth
  apiversion=9
  */

  class SimpleAuth implements Plugin
  {
    private $api, $server, $config, $sessions, $lastBroadcast = 0;

    public function __construct(ServerAPI $api, $server = false)
    {
      $this->api      = $api;
      $this->server   = ServerAPI::request();
      $this->sessions = array();
      SimpleAuthAPI::set($this);
    }

    public function init()
    {
      $this->config = new Config($this->api->plugin->configPath($this) . "config.yml", CONFIG_YAML, array(
        "allowChat"          => false,
        "messageInterval"    => 5,
        "timeout"            => 60,
        "allowRegister"      => true,
        "forceSingleSession" => true,
      ));

      $this->playerFile = new Config($this->api->plugin->configPath($this) . "players.yml", CONFIG_YAML, array());
      //$this->convert();
      $this->api->addHandler("player.quit", array($this, "quit_eventHandler"), 50);
      $this->api->addHandler("player.connect", array($this, "connect_eventHandler"), 50);
      $this->api->addHandler("player.spawn", array($this, "spawn_eventHandler"), 50);
      $this->api->addHandler("player.respawn", array($this, "respawn_eventHandler"), 50);
      $this->api->addHandler("player.chat", array($this, "chat_eventHandler"), 50);
      $this->api->addHandler("console.command", array($this, "command_eventHandler"), 50);
      $this->api->addHandler("op.check", array($this, "opcheck_eventHandler"), 50);
      $this->api->schedule(20, array($this, "checkTimer"), array(), true);
      $this->api->console->register("unregister", "<password>", array($this, "unregister_commandHandler"));
      $this->api->console->register("passwd", "<password>", array($this, "passwd_commandHandler"));
      $this->api->ban->cmdWhitelist("unregister");
      $this->api->ban->cmdWhitelist("passwd");
      console("[INFO] SimpleAuth enabled!");
    }

    public function convert()
    {
      $path = $this->api->plugin->configPath($this);
      console($path);
      $path = str_replace("SimpleAuth", "ResidentManager", $path);
      console($path);
      $cfg = $this->api->plugin->readYAML($path . "config.yml");
      foreach ($cfg as $key => $value) {
        $data = array(
          "registerdate" => time() - 1000000,
          "logindate"    => $value['last'],
          "hash"         => $value['password']
        );
        $this->playerFile->set($key, $data);
        $this->playerFile->save();
      }

    }

    public function unregister_commandHandler($cmd, $params, $issuer, $alias)
    {
      $output = "";
      if (!($issuer instanceof Player)) {
        if (isset($params[0])) {
          $this->playerFile->remove(strtolower($params[0]));
          $output .= "[SimpleAuth] Unregistered {$params[0]} correctly.\n";
        } else {
          $output .= "[SimpleAuth] Usage unregister nick\n";
        }
        return $output;
      }
      if ($this->sessions[$issuer->CID] !== true) {
        $output .= "Please login first.\n";
        return $output;
      }
      $d = $this->playerFile->get($issuer->iusername);
      if ($d !== false and $d["hash"] === sha1(implode(" ", $params))) {
        $this->playerFile->remove($issuer->iusername);
        $this->logout($issuer);
        $output .= "[SimpleAuth] Unregistered correctly.\n";
      } else {
        $output .= "[SimpleAuth] Error during authentication.\n";
      }
      return $output;
    }

    public function passwd_commandHandler($cmd, $params, $issuer, $alias)
    {
      $output = "";
      if (!($issuer instanceof Player)) {
        if (isset($params[0])) {
          $d = $this->playerFile->get($params[0]);
          if ($d === false) {
            $data = array(
              "registerdate" => time(),
              "logindate"    => time(),
              "hash"         => sha1($params[1]),
            );
            $this->playerFile->set($params[0], $data);
            $this->playerFile->save();
          }
          $output .= "[SimpleAuth] passwd {$params[0]} {$params[1]} correctly.\n";
        } else {
          $output .= "[SimpleAuth] Usage passwd pass\n";
        }
        return $output;
      }
      if ($this->sessions[$issuer->CID] !== true) {
        $output .= "Please login first.\n";
        return $output;
      }
      $d = $this->playerFile->get($issuer->iusername);
      if ($d === false) {
        $data = array(
          "registerdate" => time(),
          "logindate"    => time(),
          "hash"         => sha1($params[0]),
        );
        $this->playerFile->set($issuer->iusername, $data);
        $this->playerFile->save();
        $output .="[SimpleAuth] password changed";
      }
      return $output;
    }

    public function checkTimer()
    {
      if ($this->config->get("allowRegister") !== false and ($this->lastBroadcast + $this->config->get("messageInterval")) <= time()) {
        $broadcast           = true;
        $this->lastBroadcast = time();
      } else {
        $broadcast = false;
      }

      if (($timeout = $this->config->get("timeout")) <= 0) {
        $timeout = false;
      }

      foreach ($this->sessions as $CID => $timer) {
        if ($timer !== true and $timer !== false) {
          if ($broadcast === true) {
            $d = $this->playerFile->get($this->server->clients[$CID]->iusername);
            if ($d === false) {
              $this->server->clients[$CID]->sendChat("[SimpleAuth] You must register using /register <password>");
            } else {
              $this->server->clients[$CID]->sendChat("[SimpleAuth] You must authenticate using /login <password>");
            }
          }
          if ($timeout !== false and ($timer + $timeout) <= time()) {
            $this->server->clients[$CID]->close("authentication timeout");
          }
        }
      }

    }

    public function checkLogin(Player $player, $password)
    {

      $d = $this->playerFile->get($player->iusername);

      if ($d !== false and $d["hash"] === sha1($password)) {
        return true;
      }
      return false;
    }

    public function login(Player $player)
    {
      $d = $this->playerFile->get($player->iusername);
      if ($d !== false) {
        $d["logindate"] = time();
        $this->playerFile->set($player->iusername, $d);
        $this->playerFile->save();
      }
      $this->sessions[$player->CID] = true;
      $player->blocked              = false;
      $player->sendChat("[SimpleAuth] You've been authenticated.");
      return true;
    }

    public function logout(Player $player)
    {
      $this->sessions[$player->CID] = time();
      $player->blocked              = true;
    }

    public function register(Player $player, $password)
    {
      $d = $this->playerFile->get($player->iusername);
      if ($d === false) {
        $data = array(
          "registerdate" => time(),
          "logindate"    => time(),
          "hash"         => sha1($password),
        );
        $this->playerFile->set($player->iusername, $data);
        $this->playerFile->save();
        return true;
      }
      return false;
    }

    public function quit_eventHandler($data, $event)
    {
      unset($this->sessions[$data->CID]);
    }

    public function connect_eventHandler($data, $event)
    {
      if ($this->config->get("forceSingleSession") === true) {
        $p = $this->api->player->get($data->iusername);
        if (($p instanceof Player) and $p->iusername === $data->iusername) {
          return false;
        }
      }
      $this->sessions[$data->CID] = false;
    }

    public function spawn_eventHandler($data, $event)
    {
      if ($this->sessions[$data->CID] !== true) {
        $this->sessions[$data->CID] = time();
        $data->blocked              = true;
        $data->sendChat("[SimpleAuth] This server uses SimpleAuth to protect your account.");
        if ($this->config->get("allowRegister") !== false) {
          $d = $this->playerFile->get($data->iusername);
          if ($d === false) {
            $data->sendChat("[SimpleAuth] You must register using /register <password>");
          } else {
            $data->sendChat("[SimpleAuth] You must authenticate using /login <password>");
          }
        }
      }
    }

    public function respawn_eventHandler($data, $event)
    {
      if ($this->sessions[$data->CID] !== true) {
        $data->blocked = true;
      }
    }

    public function chat_eventHandler($data, $event)
    {
      if ($this->config->get("allowChat") !== true and $this->sessions[$data["player"]->CID] !== true) {
        return false;
      }
    }

    public function command_eventHandler($data, $event)
    {
      if (($data["issuer"] instanceof Player) and $this->sessions[$data["issuer"]->CID] !== true) {
        if ($this->config->get("allowRegister") !== false and $data["cmd"] === "login" and $this->checkLogin($data["issuer"], implode(" ", $data["parameters"])) === true) {
          $this->login($data["issuer"]);
          return true;
        } elseif ($this->config->get("allowRegister") !== false and $data["cmd"] === "register" and $this->register($data["issuer"], implode(" ", $data["parameters"])) === true) {
          $data["issuer"]->sendChat("[SimpleAuth] You've been sucesfully registered.");
          $this->login($data["issuer"]);
          return true;
        } elseif ($this->config->get("allowRegister") !== false and $data["cmd"] === "login" or $data["cmd"] === "register") {
          $data["issuer"]->sendChat("[SimpleAuth] Error during authentication.");
          return true;
        }
        return false;
      }
    }

    public function opcheck_eventHandler($data, $event)
    {
      $p = $this->api->player->get($data);
      if (($p instanceof Player) and $this->sessions[$p->CID] !== true) {
        return false;
      }
    }

    public function __destruct()
    {
      $this->config->save();
      $this->playerFile->save();
    }

  }

  class SimpleAuthAPI
  {
    private static $object;

    public static function set(SimpleAuth $plugin)
    {
      if (SimpleAuthAPI::$object instanceof SimpleAuth) {
        return false;
      }
      SimpleAuthAPI::$object = $plugin;
    }

    public static function get()
    {
      return SimpleAuthAPI::$object;
    }

    public static function login(Player $player)
    {
      return SimpleAuthAPI::$object->login($player);
    }

    public static function logout(Player $player)
    {
      return SimpleAuthAPI::$object->logout($player);
    }
  }