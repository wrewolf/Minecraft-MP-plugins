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
      $this->api->console->register("group", "group add|rm|ls|lsu <user> <group>", array($this, "commandHandler"));
      $this->api->console->register("lsgroup", "lsgroup list of groups", array($this, "commandHandler"));
      $this->api->console->register("rmgroup", "rmgroup <group>", array($this, "commandHandler"));
      $this->api->console->register("addgroup", "addgroup <group>", array($this, "commandHandler"));
      $this->api->console->register("reloadgroup", "reload group config file", array($this, "commandHandler"));
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
              $user  = strtolower(array_shift($params));
              $group = strtolower(array_shift($params));
              if ($issuer != "console") {
                if (!$this->api->dhandle("group.groups", array('user' => $issuer->username))) {
                  $output .= "[SimpleGroup] You not member of $group group";
                  break;
                }
              }
              if ($user == "" or $group == "") {
                $output .= "[SimpleGroup] Not set user or group name";
                break;
              }
              $this->config[$group][] = $user;
              $this->api->plugin->writeYAML($this->path . "config.yml", $this->config);
              $output .= "[SimpleGroup] User $user added to $group group";
              break;
            case "rm":
              $user  = strtolower(array_shift($params));
              $group = strtolower(array_shift($params));
              if ($issuer != "console") {
                if (!$this->api->dhandle("group.groups", array('user' => $issuer->username))) {
                  $output .= "[SimpleGroup] You not member of $group group";
                  break;
                }
              }
              if ($user == "" or $group == "") {
                $output .= "[SimpleGroup] Not set user or group name";
                break;
              }
              unset($this->config[$group][array_search($user, $this->config[$group])]);
              $this->api->plugin->writeYAML($this->path . "config.yml", $this->config);
              $output .= "[SimpleGroup] User $user removed to $group group";
              break;
            case "lsu":
              if ($issuer != "console") {
                $user = $issuer->username;
              } else {
                $user = strtolower(array_shift($params));
              }
              $output .= "[SimpleGroup] User $user groups: ";
              $output .= $this->api->dhandle("group.groups", array('user' => $user));
              break;
            case "ls":
              $group = strtolower(array_shift($params));
              if ($issuer != "console") {
                if (!$this->api->dhandle("group.groups", array('user' => $issuer->username))) {
                  $output .= "[SimpleGroup] You not member of $group group";
                  break;
                }
              }
              $output .= "[SimpleGroup] Group $group contain: ";
              $output .= implode(", ", $this->config[$group]);
              break;
          }
          break;
        case "groupprint":
          $output .= "[SimpleGroup] Dump: " . print_r($this->config, true);
          break;
        case "lsgroup":
          $g = array();
          foreach ($this->config as $key => $value) {
            $g[] = $key;
          }

          $output .= "[SimpleGroup] List: " . implode(', ', $g);
          break;
        case "rmgroup":
          $group = strtolower(array_shift($params));
          if ($group == "") {
            $output = "[SimpleGroup] Not set target group name";
            break;
          }
          unset($this->config[$group]);
          $this->api->plugin->writeYAML($this->path . "config.yml", $this->config);
          $output .= "[SimpleGroup] Group $group removed";
          break;
        case "addgroup":
          $group = strtolower(array_shift($params));
          if ($group == "") {
            $output = "[SimpleGroup] Not set target group name";
            break;
          }
          if (!in_array($group, $this->config)) {
            $this->config[]       = $params[0];
            $this->config[$group] = array();

            $user                   = $issuer->username;
            $this->config[$group][] = $user;

            $this->api->plugin->writeYAML($this->path . "config.yml", $this->config);
            $output .= "[SimpleGroup] Group $group added\nUser $user added to $group group";
          } else {
            $output .= "[SimpleGroup] Group $group exist";
          }
          break;
        case "reloadgroup":
          if ($issuer != "console") {
            $output .= "[SimpleGroup] Only for Console";
            break;
          }
          $this->config = $this->api->plugin->readYAML($this->path . "config.yml");
          $output .= "[SimpleGroup] Config reloaded";
          break;
        case "":
          $output .= "[SimpleGroup] Usage:\ngroup add|rm|ls|lsu <user> <group>\nlsu - list user groups, ls list group users\nlsgroup list of groups\nrmgroup <group>\naddgroup <group>";
          break;
      }
      return $output;
    }
  }