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
    private $api, $server, $path, $config;

    public function __construct(ServerAPI $api, $server = false)
    {
      $this->api    = $api;
      $this->server = $server;
    }

    public function init()
    {
      $this->api->console->register("sethome", "/sethome Set user home", array($this, "commandH"));
      $this->api->ban->cmdWhitelist("sethome");
      $this->api->console->register("home", "/home Go to home", array($this, "commandH"));
      $this->api->ban->cmdWhitelist("home");
      $this->path   = $this->api->plugin->createConfig($this, array());
      $this->config = $this->api->plugin->readYAML($this->path . "config.yml");
    }

    public function __destruct()
    {
    }

    public function commandH($cmd, $params, $issuer, $alias)
    {
      $output = "";
      switch ($cmd) {
        case "home":
          if (!($issuer instanceof Player)) {
            $output .= "Please run this command in-game.\n";
            break;
          }
          if (isset($this->config[$issuer->iusername])) {
            $home = $this->config[$issuer->iusername];
            $name = $issuer->iusername;
            if ($home["world"] !== $issuer->level->getName()) {
              $this->api->player->teleport($name, "w:" . $home["world"]);
            }
            $this->api->player->tppos($name, $home["x"], $home["y"], $home["z"]);
          } else {
            $output .= "You do not have a home.\n";
          }
          break;
        case "sethome":
          if (!($issuer instanceof Player)) {
            $output .= "Please run this command in-game.\n";
            break;
          }
          $this->config[$issuer->iusername] = array(
            "world" => $issuer->level->getName(),
            "x"     => $issuer->entity->x,
            "y"     => $issuer->entity->y,
            "z"     => $issuer->entity->z,
          );
          $this->api->plugin->writeYAML($this->path . "config.yml", $this->config);
          $output .= "Home set correctly!\n";
          break;
        case "delhome":
          if (!($issuer instanceof Player)) {
            $output .= "Please run this command in-game.\n";
            break;
          }
          unset($this->config[$issuer->iusername]);
          $this->api->plugin->writeYAML($this->path . "config.yml", $this->config);
          $output .= "Your home has been deleted.\n";
          break;
      }

      return $output;
    }
  }