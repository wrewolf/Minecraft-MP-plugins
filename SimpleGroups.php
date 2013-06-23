<?php
  /*
   __PocketMine Plugin__
  name=SimpleGroups
  description=Simple groups
  version=0.1
  author=WreWolf
  class=SimpleGroups
  apiversion=9
  */

  class SimpleGroups implements Plugin
  {
    private $api, $path, $config;

    public function __construct(ServerAPI $api, $server = false)
    {
      $this->api = $api;
    }

    public function __destruct()
    {

    }

    public function init()
    {
      //$this->api->addHandler("player.join", array($this, "eventHandler"));
      $this->api->addHandler("group.existOf", array($this, "eventHandler"));
      $this->api->addHandler("group.groups", array($this, "eventHandler"));
      $this->api->console->register("group", "group add|rm group user", array($this, "commandHandler"));
      $this->api->console->register("lsgroup", "lsgroup list of groups", array($this, "commandHandler"));
      $this->api->console->register("rmgroup", "rmgroup", array($this, "commandHandler"));
      $this->api->console->register("addgroup", "addgroup", array($this, "commandHandler"));
      $this->path   = $this->api->plugin->createConfig($this, array());
      $this->config = $this->api->plugin->readYAML($this->path . "config.yml");
      //console("SimpleGroups Inited");
    }

    public function eventHandler($data, $event)
    {
      $output = "";
      switch ($event) {
        case "group.existOf":
          $user  = $data["user"];
          $group = $data["group"];
          $rez   = false;
          if (isset($this->config[$group]))
            $rez = in_array($user, $this->config[$group]);
          return $rez;
          break;
        case "group.groups":
          $user = $data["user"];
          $gg   = array();
          foreach ($this->config as $gname => $groups) {
            if (in_array($user, $groups))
              $gg[] = $gname;
          }
          $output .= implode(", ", $gg);
          break;
          //  case "player.join":
          //  break;
      }
      return $output;
    }

    public function commandHandler($cmd, $params, $issuer, $alias)
    {
      $output = "";
      $cmd    = strtolower($cmd);
      switch ($cmd) {
        case "group":
          $command = array_shift($params);
          switch ($command) {
            case "add":
              $user  = array_shift($params);
              $group = array_shift($params);

              $this->config[$group][] = $user;
              $this->api->plugin->writeYAML($this->path . "config.yml", $this->config);
              break;
            case "rm":
              $user  = array_shift($params);
              $group = array_shift($params);

              unset($this->config[$group][array_search($user, $this->config[$group])]);
              $this->api->plugin->writeYAML($this->path . "config.yml", $this->config);
              break;
            case "ls":
              $output .= $this->api->dhandle("group.groups", array('user' => array_shift($params)));
              break;
          }
          break;
        case "lsgroup":
          $g = array();
          foreach ($this->config as $key => $value) {
            $g[] = $key;
          }
          $output = implode(', ', $g);
          break;
        case "rmgroup":
          unset($this->config[$params[0]]);
          $this->api->plugin->writeYAML($this->path . "config.yml", $this->config);
          break;
        case "addgroup":
          if (!in_array($params[0], $this->config)) {
            $this->config[]           = $params[0];
            $this->config[$params[0]] = array();
            $this->api->plugin->writeYAML($this->path . "config.yml", $this->config);
            $output .= "Group added";
          } else {
            $output .= "Group exist";
          }
          break;
      }
      return $output;
    }
  }