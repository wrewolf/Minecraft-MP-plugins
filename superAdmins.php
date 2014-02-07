<?php
  /*
__PocketMine Plugin__
name=superAdmins
description=superAdmins
version=0.5
author=WreWolf
class=superAdmins
apiversion=11
*/

  class superAdmins implements Plugin
  {
    private $api, $path, $server, $config;

    public function __construct(ServerAPI $api, $server = false)
    {
      $this->api    = $api;
      $this->server = ServerAPI::request();
    }

    public function init()
    {
      $this->path   = $this->api->plugin->createConfig($this, array());
      $this->config = $this->api->plugin->readYAML($this->path . "config.yml");
      $this->api->addHandler("admin.isSa", array($this, "eventHandler"));
      $this->api->console->register("addsa", "", array($this, "addsaHandler"));
      $this->api->console->register("rmsa", "", array($this, "rmsaHandler"));
      $this->api->console->register("lssa", "superAdmins command", array($this, "lssaHandler"));
    }

    public function addsaHandler($cmd, $args, $issuer, $alias)
    {
      if ($issuer != "console")
      {
        return "Only from Console";
      }
      $output = "Alredy in";
      $user   = $args[0];
      if (!in_array($user, $this->config)) {
        $this->config[] = $user;
        $this->api->plugin->writeYAML($this->path . "config.yml", $this->config);
        $output = "Added";
      }
      return $output;
    }

    public function rmsaHandler($cmd, $args, $issuer, $alias)
    {
      if ($issuer != "console")
      {
        return "Only from Console";
      }
      $output = "Not in sa";
      $user   = $args[0];
      if (($key = array_search($user, $this->config)) !== false) {
        unset($this->config[$key]);
        $output = "Removed";
      }

      return $output;
    }

    public function lssaHandler($cmd, $args, $issuer, $alias)
    {
      if ($issuer != "console")
      {
        return="Only from Console";
      }
      return "Sa is: " . implode(',', $this->config);
    }

    public function eventHandler($data, $event)
    {
      return in_array($data['user'], $this->config);
    }

    public function __destruct()
    {
    }
  }
