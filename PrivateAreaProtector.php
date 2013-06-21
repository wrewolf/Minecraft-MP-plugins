<?php
  /*^M
  __PocketMine Plugin__
  name=PrivateAreaProtection
  description=Private Area Multi world based on Private Area Protection by shoghicp
  version=0.3
  author=shoghicp
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
      $this->api->ban->cmdWhitelist("unprotect");
      $this->api->ban->cmdWhitelist("protect");
      $this->api->addHandler("player.block.place", array($this, "handle"), 7);
      $this->api->addHandler("player.block.touch", array($this, "handle"), 7);
      $this->api->addHandler("player.block.break", array($this, "handle"), 7);
      $this->api->console->alias("protect1", "protect pos1");
      $this->api->console->alias("protect2", "protect pos2");
    }

    public function __destruct()
    {
    }

    public function commandH($cmd, $params, $issuer, $alias)
    {
      console("command handeler");
      $output = "";
      if ($issuer == "console") {
        console("command handeler console");
        switch ($cmd) {
          case "protect":
            console("command handeler console protect");
            foreach ($this->config as $key => $level) {
              foreach ($level as $uname=>$name) {
                foreach ($name as $config) {

                  if ($config['protect']) {
                    $protect = "1";
                  } else {
                    $protect = "0";
                  }
                  $min = implode(",", $config['min']);
                  $max = implode(",", $config['max']);
                  $output .= "[\x1b[32mPROTECT: " . $protect . "\x1b[0m] " . $uname . "'s area (\x1b[36m" . $min . "\x1b[0m)-(\x1b[36m" . $max . "\x1b[0m) : $key\n";
                }
              }
            }
            break;
          case
          "unprotect":
            console("command handeler console unprotect");
            $level = array_shift($params);
            $user  = array_shift($params);
            $area  = array_shift($params);
            if (empty($user)) {
              $output .= "Usage: /unprotect <level> <area owner> <area>\n";
              break;
            }
            if (!$this->config[$level][$user][$area]['protect']) {
              $output .= "His area is not protected.\n";
              break;
            }
            $this->config[$level][$user][$area]['protect'] = false;
            $this->writeConfig($this->config);
            $output .= "Lifted the protection.\n";
            break;
        }
      } elseif ($issuer instanceof Player) {
        console("command handeler console");
        $user = $issuer->username;
        switch ($cmd) {
          case "protect":
            console("command handeler user protect");
            $mode = array_shift($params);
            switch ($mode) {
              case "":
                if (!isset($this->pos1[$user]) or !isset($this->pos2[$user])) {
                  $output .= "Make a selection first.\nUsage: /protect <pos1 | pos2>\n";
                  break;
                }
                $pos1                          = $this->pos1[$user];
                $pos2                          = $this->pos2[$user];
                $minX                          = min($pos1[0], $pos2[0]);
                $maxX                          = max($pos1[0], $pos2[0]);
                $minY                          = min($pos1[1], $pos2[1]);
                $maxY                          = max($pos1[1], $pos2[1]);
                $minZ                          = min($pos1[2], $pos2[2]);
                $maxZ                          = max($pos1[2], $pos2[2]);
                $max                           = array($maxX, $maxY, $maxZ);
                $min                           = array($minX, $minY, $minZ);
                $level                         = $issuer->entity->level->getName();
                $this->config[$level][$user][] = array("protect" => true, "min" => $min, "max" => $max);
                $this->writeConfig($this->config);
                $output .= "Protected this area ($minX, $minY, $minZ)-($maxX, $maxY, $maxZ) : $level\n";
                break;
              case "pos1":
              case "1":
                console("command handeler user pos1");
                $x                 = round($issuer->entity->x - 0.5);
                $y                 = round($issuer->entity->y);
                $z                 = round($issuer->entity->z - 0.5);
                $this->pos1[$user] = array($x, $y, $z);
                $output .= "[AreaProtector] First position set to (" . $this->pos1[$user][0] . ", " . $this->pos1[$user][1] . ", " . $this->pos1[$user][2] . ")\n";
                break;
              case "pos2":
              case "2":
                console("command handeler user pos2");
                $x                 = round($issuer->entity->x - 0.5);
                $y                 = round($issuer->entity->y);
                $z                 = round($issuer->entity->z - 0.5);
                $this->pos2[$user] = array($x, $y, $z);
                $output .= "[AreaProtector] Second position set to (" . $this->pos2[$user][0] . ", " . $this->pos2[$user][1] . ", " . $this->pos2[$user][2] . ")\n";
                break;
              default:
                $output .= "Usage: /protect\nUsage: /protect pos1\nUsage: /protect pos2\n";
            }
            break;
          case "unprotect":
            if (!$this->config[$user]['protect']) {
              $output .= "Your area is not protected.\n";
              break;
            }
            $level = $issuer->entity->level->getName();
            $area  = null;
            foreach ($this->config[$level][$user] as $Area) {
              if ($Area['min'][0] <= $issuer->entity->x and $issuer->entity->x <= $Area['max'][0]) {
                if ($Area['min'][1] <= $issuer->entity->y and $issuer->entity->y <= $Area['max'][1]) {
                  if ($Area['min'][2] <= $issuer->entity->z and $issuer->entity->z <= $Area['max'][2]) {
                    $area = $Area;
                    break;
                  }
                }
              }
            }
            if ($area !== null) {
              $this->config[$level][$user][$area]['protect'] = false;
              $this->writeConfig($this->config);
              $output .= "Lifted the protection.\n";
            } else {
              $output .= "This area is not protected.\n";
            }
            break;
        }
      }
      return $output;
    }

    public function checkProtect($data, $name, $config)
    {
//      console("{$config['min'][0]},{$config['min'][1]},{$config['min'][2]}  {$config['max'][0]},{$config['max'][1]},{$config['max'][2]}  : {$data['target']->x},{$data['target']->y},{$data['target']->z} ");
      if ($config['min'][0] <= $data['target']->x and $data['target']->x <= $config['max'][0]) {
        if ($config['min'][1] <= $data['target']->y and $data['target']->y <= $config['max'][1]) {
          if ($config['min'][2] <= $data['target']->z and $data['target']->z <= $config['max'][2]) {
            $this->api->chat->sendTo(false, "This is $name's private area.", $data['player']->username);
            return true;
          }
        }
      }
      return false;
    }

    public function handle($data, $event)
    {
      switch ($event) {
        case 'player.block.break':
        case 'player.block.touch':
        case 'player.block.place':
          $level = $data['player']->entity->level->getName();
          foreach ($this->config[$level] as $uname=>$name) {
            foreach ($name as $config) {
              if (!$config['protect'] or $uname == $data['player']->username) {
                continue;
              }
              if ($this->checkProtect($data, $uname, $config))
                return false;
            }
          }
          break;
      }
    }

    public function createConfig()
    {
      $this->path   = $this->api->plugin->createConfig($this, array($this->api->level->getDefault()->getName()=>array()));
      $this->config = $this->api->plugin->readYAML($this->path . "config.yml");
    }

    public function writeConfig($data)
    {
      $this->api->plugin->writeYAML($this->path . "config.yml", $data);
    }
  }