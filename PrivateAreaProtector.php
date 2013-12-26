<?php
  /*
  __PocketMine Plugin__
  name=PrivateAreaProtection
  description=Private Area Multi world based on Private Area Protection by shoghicp
  version=1
  author=WreWolf
  class=PrivateAreaProtector
  apiversion=9
  */

  class PrivateAreaProtector implements Plugin
  {
    private $api, $config, $path, $pos1, $pos2;

    public function __construct(ServerAPI $api, $server = false)
    {
      $this->api  = $api;
      $this->pos1 = array();
      $this->pos2 = array();
    }

    public function init()
    {
      $this->createConfig();
      $this->api->console->register("unprotect", "Unprotects your private area.", array($this, "commandH"));
      $this->api->console->register("protect", "Protects the area for you.", array($this, "commandH"));
      $this->api->console->register("sprotect", "Special Protects the area for console.", array($this, "commandH"));
      $this->api->ban->cmdWhitelist("unprotect");
      $this->api->ban->cmdWhitelist("protect");
      $this->api->addHandler("player.block.place", array($this, "handle"), 7);
      //$this->api->addHandler("player.block.touch", array($this, "handle"), 7);
      $this->api->addHandler("player.block.break", array($this, "handle"), 7);
      $this->api->console->alias("protect1", "protect pos1");
      $this->api->console->alias("protect2", "protect pos2");
    }

    public function __destruct()
    {
    }

    public function commandH($cmd, $params, $issuer, $alias)
    {
      $this->debug_print("command handeler");
      $output = "";
      if ($issuer == "console") {
        $this->debug_print("command handeler console");
        switch ($cmd) {
          case "sprotect":
            if (count($params) == 8) {
              $owner = array_shift($params);
              $level = array_shift($params);
              $pos1  = array(array_shift($params), array_shift($params), array_shift($params));
              $pos2  = array(array_shift($params), array_shift($params), array_shift($params));
              $minX  = min($pos1[0], $pos2[0]);
              $maxX  = max($pos1[0], $pos2[0]);
              $minY  = min($pos1[1], $pos2[1]);
              $maxY  = max($pos1[1], $pos2[1]);
              $minZ  = min($pos1[2], $pos2[2]);
              $maxZ  = max($pos1[2], $pos2[2]);
              $max   = array($maxX, $maxY, $maxZ);
              $min   = array($minX, $minY, $minZ);

              $this->config[$level][$owner] = array("protect" => true, "min" => $min, "max" => $max);
              $this->writeConfig($this->config);
              $output .= "[AreaProtector] Protected this area ($minX, $minY, $minZ)-($maxX, $maxY, $maxZ) : $level\n";
            } else {
              $output .= "[AreaProtector] Usage sprotect nick level x1 y1 z1 x2 y2 z2";
            }
            break;
          case "protect":
            $this->debug_print("command handeler console protect");
            foreach ($this->config as $key => $level) {
              foreach ($level as $name => $config) {
                if ($config['protect']) {
                  $protect = "1";
                } else {
                  $protect = "0";
                }
                $min = implode(",", $config['min']);
                $max = implode(",", $config['max']);
                $output .= "[AreaProtector] [\x1b[32mPROTECT: " . $protect . "\x1b[0m] " . $name . "'s area (\x1b[36m" . $min . "\x1b[0m)-(\x1b[36m" . $max . "\x1b[0m) : $key\n";
              }
            }
            break;
          case
          "unprotect":
            //console("command handeler console unprotect");
            $level = array_shift($params);
            $user  = array_shift($params);
            if (empty($user)) {
              $output .= "[AreaProtector] Usage: /unprotect <level> <area owner>\n";
              break;
            }
            if (!$this->config[$level][$user]['protect']) {
              $output .= "[AreaProtector] His area is not protected.\n";
              break;
            }
            $this->config[$level][$user]['protect'] = false;
            $this->writeConfig($this->config);
            $output .= "[AreaProtector] Lifted the protection.\n";
            break;
        }
      } elseif ($issuer instanceof Player) {
        $this->debug_print("command handeler payer");
        $user = $issuer->username;
        switch ($cmd) {
          case "protect":
            $this->debug_print("command handeler user protect");
            $mode = array_shift($params);
            switch ($mode) {
              case "g";
                $group            = array_shift($params);
                $usercheckingroup = $this->api->dhandle("group.existOf", array('user' => $user, "group" => $group));
                if (!$usercheckingroup) {
                  $output .= "[AreaProtector] You are not a member of a group $group\n";
                  break;
                }
              case "":
                if (!isset($this->pos1[$user]) or !isset($this->pos2[$user])) {
                  $output .= "[AreaProtector] Make a selection first.\nUsage: /protect <pos1 | pos2>\n";
                  break;
                }
                $pos1  = $this->pos1[$user];
                $pos2  = $this->pos2[$user];
                $minX  = min($pos1[0], $pos2[0]);
                $maxX  = max($pos1[0], $pos2[0]);
                $minY  = min($pos1[1], $pos2[1]);
                $maxY  = max($pos1[1], $pos2[1]);
                $minZ  = min($pos1[2], $pos2[2]);
                $maxZ  = max($pos1[2], $pos2[2]);
                $max   = array($maxX, $maxY, $maxZ);
                $min   = array($minX, $minY, $minZ);
                $level = $issuer->entity->level->getName();
                if ($mode == "") {
                  $this->config[$level][$user] = array("protect" => true, "min" => $min, "max" => $max);
                } else {
                  $this->config[$level]["g:" . $group] = array("protect" => true, "min" => $min, "max" => $max);
                }
                $this->writeConfig($this->config);
                $output .= "[AreaProtector] Protected this area ($minX, $minY, $minZ)-($maxX, $maxY, $maxZ) : $level\n";
                break;
              case "pos1":
              case "1":
                //console("command handeler user pos1");
                $x                 = round($issuer->entity->x - 0.5);
                $y                 = round($issuer->entity->y);
                $z                 = round($issuer->entity->z - 0.5);
                $this->pos1[$user] = array($x, $y, $z);
                $output .= "[AreaProtector] First position set to (" . $this->pos1[$user][0] . ", " . $this->pos1[$user][1] . ", " . $this->pos1[$user][2] . ")\n";
                break;
              case "pos2":
              case "2":
                //console("command handeler user pos2");
                $x                 = round($issuer->entity->x - 0.5);
                $y                 = round($issuer->entity->y);
                $z                 = round($issuer->entity->z - 0.5);
                $this->pos2[$user] = array($x, $y, $z);
                $output .= "[AreaProtector] Second position set to (" . $this->pos2[$user][0] . ", " . $this->pos2[$user][1] . ", " . $this->pos2[$user][2] . ")\n";
                break;
              default:
                $output .= "Usage: /protect\nUsage: /protect1\nUsage: /protect2\n";
            }
            break;
          case "unprotect":
            if (!$this->config[$user]['protect']) {
              $output .= "[AreaProtector] Your area is not protected.\n";
              break;
            }
            $level = $issuer->entity->level->getName();
            if (isset($this->config[$level][$user]['protect'])) {
              if ($this->config[$level][$user]['protect']) {
                $this->config[$level][$user]['protect'] = false;
                unset($this->config[$level][$user]);
                $this->writeConfig($this->config);
                $output .= "[AreaProtector] Lifted the protection.\n";
              } else {
                $output .= "[AreaProtector] This area is not protected.\n";
              }
            } else {
              $output .= "[AreaProtector] This area is not protected.\n";
            }
            break;
        }
      }
      return $output;
    }

    public function checkProtect($data, $name, $config)
    {
      $this->debug_print("{$config['min'][0]},{$config['min'][1]},{$config['min'][2]}  {$config['max'][0]},{$config['max'][1]},{$config['max'][2]}  : {$data['target']->x},{$data['target']->y},{$data['target']->z} ");
      if ($this->api->dhandle("admin.isSa", array('user' => $data['player']->username))){
        console("sa");
        return false;
      }
      if ($config['min'][0] <= $data['target']->x and $data['target']->x <= $config['max'][0]) {
        if ($config['min'][1] <= $data['target']->y and $data['target']->y <= $config['max'][1]) {
          if ($config['min'][2] <= $data['target']->z and $data['target']->z <= $config['max'][2]) {
            if (substr($name, 0, 2) == "g:") {
              $usercheckingroup = $this->api->dhandle("group.existOf", array('user' => $data['player']->username, "group" => substr($name, 2)));
              if (!$usercheckingroup) {
                $name = substr($name, 2);
                $this->api->chat->sendTo(false, "[AreaProtector] This is Group $name's private area.", $data['player']->username);
                return true;
              }
            } else {
              $this->api->chat->sendTo(false, "[AreaProtector] This is $name's private area.", $data['player']->username);
              return true;
            }
          }
        }
      }

      return false;
    }

    public function handle($data, $event)
    {
      switch ($event) {
        case 'player.block.touch':
          //console("touch PrivateAreaProtector");
        case 'player.block.break':
        case 'player.block.place':
          $level = $data['player']->entity->level->getName();
          foreach ($this->config[$level] as $name => $config) {
            if (!$config['protect'] or $name == $data['player']->username or $name == "ww")
              continue;
            if ($this->checkProtect($data, $name, $config))
              return false;
          }
          break;
      }
    }

    public function createConfig()
    {
      $this->path   = $this->api->plugin->createConfig($this, array($this->api->level->getDefault()->getName() => array()));
      $this->config = $this->api->plugin->readYAML($this->path . "config.yml");
    }

    public function writeConfig($data)
    {
      $this->api->plugin->writeYAML($this->path . "config.yml", $data);
    }

    function debug_print($str)
    {
      //console($str);
    }
  }