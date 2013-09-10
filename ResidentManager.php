<?php
  /*
__PocketMine Plugin__
name=ResidentManager
description=ResidentManager
version=0.5
author=shoghicp
class=ResidentManager
apiversion=6
*/

  class ResidentManager implements Plugin
  {
    private $api, $path, $config, $login;

    public function __construct(ServerAPI $api, $server = false)
    {
      $this->api   = $api;
      $this->login = array();
    }

    public function init()
    {
      $this->createConfig();
      $this->api->addHandler('console.command', array($this, 'handle'), 24);
      $this->api->addHandler('player.container.slot', array($this, 'handle'), 19);
      $this->api->addHandler('player.pickup', array($this, 'handle'), 19);
      $this->api->addHandler('player.block.break', array($this, 'handle'), 3);
      $this->api->addHandler('player.block.place', array($this, 'handle'), 3);
      $this->api->event('player.join', array($this, 'handle'));
      $this->api->console->register('register', 'Register yourself on the server.', array($this, 'commandH'));
      $this->api->console->register('login', 'Login to the server.', array($this, 'commandH'));
      $this->api->console->register('logout', 'Logout from the server.', array($this, 'commandH'));
      $this->api->console->register('changepassword', 'Change your password.', array($this, 'commandH'));
      $this->api->console->register('unregister', 'Unregister from the server.', array($this, 'commandH'));
      $this->api->console->register('purge', 'Delete players from database that haven\'t logged in for X days.', array($this, 'commandH'));
      $this->api->console->register('resident', 'Show resident list.', array($this, 'commandH'));
      $this->api->console->alias('passwd', 'changepassword');
      $this->api->ban->cmdWhitelist("register");
      $this->api->ban->cmdWhitelist("login");
    }

    public function __destruct()
    {
    }

    public function handle($data, $event)
    {
      switch ($event) {
        case "player.block.place":
          $user = $data['player']->username;
          if($user==="")break;
          if ($this->login[$user]) {
            break;
          }
          if (!isset($this->config[$user])) {
            $data['player']->sendChat("[INFO] You are not a resident of this server! You must register.\n[INFO] Usage: /register <password>");
          } else {
            $data['player']->sendChat("[INFO] You have to log in to this server!\n[INFO] Usage: /login <password>");
          }
          return false;
        case "player.block.break":
          $user = $data['player']->username;
          if($user==="")break;
          if ($this->login[$user]) {
            break;
          }
          if (!isset($this->config[$user])) {
            $data['player']->sendChat("[INFO] You are not a resident of this server! You must register.\n[INFO] Usage: /register <password>");
          } else {
            $data['player']->sendChat("[INFO] You have to log in to this server!\n[INFO] Usage: /login <password>");
          }
	  $this->api->console->run("give $user sign", "console", false);
          return false;
        case "console.command":
          if (!($data['issuer'] instanceof Player)) {
            break;
          }
          if (in_array($data['cmd'], array('register', 'login'))) {
            break;
          }
          $user = $data['issuer']->username;
          if($user==="")break;
          if ($this->login[$user]) {
            break;
          }
          if (!isset($this->config[$user])) {
            $data['issuer']->sendChat("[INFO] You are not a resident of this server! You must register.\n[INFO] Usage: /register <password>");
          } else {
            $data['issuer']->sendChat("[INFO] You have to log in to this server!\n[INFO] Usage: /login <password>");
          }
          return false;
        case "player.container.slot":
          $user = $data['player']->username;
          if($user==="")break;
          if ($this->login[$user]) {
            break;
          }
          if (!isset($this->config[$user])) {
            $data['player']->sendChat("[INFO] You are not a resident of this server! You must register.\n[INFO] Usage: /register <password>");
          } else {
            $data['player']->sendChat("[INFO] You have to log in to this server!\n[INFO] Usage: /login <password>");
          }
          return false;
        case "player.pickup":
          $user = $data['player']->username;
          if($user==="")break;
          if ($this->login[$user]) {
            break;
          }
          return false;
        case "player.join":
          $user               = $data->username;
          if($user==='' or $user == null) return false;
          $this->api->chat->sendTo(false,"Dobro pozhalovat' na server 4pda\nAdministratsiya servera WW, Doverennyi OP Mause\nGlavnye OPy KlaiM200, Rockey_lol\nOstal'nye OP Warrior236236, Valeross55, kosder, stalkerbelko, KAPANDAIII",$user);
          $this->login[$user] = false;
          break;
      }
    }

    public function commandH($cmd, $params, $issuer, $alias)
    {
      $output = "";
      if ($issuer instanceof Player) {
        $user = $issuer->username;
        switch ($cmd) {
          case "register":
            $password = array_shift($params);
            if (isset($this->config[$user])) {
              $output .= "[INFO] You are already a member of this server!\n";
              break;
            }
            if (empty($password)) {
              $output .= "Usage: /register <password>\n";
              break;
            }
            $hashpass                    = sha1($password);
            $this->config[$user]         = array('password' => $hashpass);
            $this->config[$user]['last'] = time();
            $this->api->plugin->writeYAML($this->path . "config.yml", $this->config);
            $this->login[$user] = true;
            $output .= "[INFO] Your registration is successful. Welcome " . $user . "!\n";
            break;
          case "login":
            $password = array_shift($params);
            if (!isset($this->config[$user])) {
              $output .= "[INFO] You are not a resident of this server! You must register.\n[INFO] Usage: /register <password>\n";
              break;
            }
            if ($this->login[$user]) {
              $output .= "[INFO] You have already be logged in!\n";
              break;
            }
            if (sha1($password) == $this->config[$user]['password']) {
              $output .= "[INFO] You logged in to server!\n";
              $this->login[$user]          = true;
              $this->config[$user]['last'] = time();
              $this->api->plugin->writeYAML($this->path . "config.yml", $this->config);
            } else {
              $output .= "[INFO] Incorrect password: \"$password\"\n";
            }
            break;
          case "logout":
            $this->login[$user] = false;
            $output .= "[INFO] Your have logged out from the server.\n";
            break;
          case "resident":
            $output .= "[INFO] Resident list:\n";
            foreach ($this->config as $name => $data) {
              $online = $this->api->player->get($name);
              if ($online) {
                $online = "[1]";
              } else {
                $online = "[0]";
              }
              $output .= $online . $name . "  ";
            }
            break;
          case "unregister":
            $password = array_shift($params);
            if (empty($password)) {
              $output .= "[INFO] Usage: /unregister <password>\n";
              break;
            }
            if (sha1($password) != $this->config[$user]['password']) {
              $output .= "[INFO] Incorrect password: \"" . $password . "\"\n";
              break;
            }
            unset($this->config[$user]);
            $this->api->plugin->writeYAML($this->path . "config.yml", $this->config);
            $this->login[$user] = false;
            $output .= "[INFO] Your account was removed!\n";
            break;
          case "changepassword":
            if (!$this->login[$user]) {
              $output .= "[INFO] You have to log in to this server!\n[INFO] Usage: /login <password>\n";
              break;
            }
            $old = array_shift($params);
            $new = array_shift($params);
            if (empty($new)) {
              $output .= "[INFO] Usage: /changepassword <old pass> <new pass>\n";
              break;
            }
            if (sha1($old) != $this->config[$user]['password']) {
              $output .= "[INFO] Incorrect password: \"" . $old . "\"\n";
              break;
            }
            $this->config[$user]['password'] = sha1($new);
            $this->api->plugin->writeYAML($this->path . "config.yml", $this->config);
            $output .= "[INFO] Your password is changed to \"" . $new . "\"\n";
            break;
          case "purge":
            $output .= "Must be run on the console.\n";
            break;
        }
      } else {
        switch ($cmd) {
          case "register":
            $user     = array_shift($params);
            $password = array_shift($params);
            if (empty($password)) {
              $output .= "Usage: /register <player> <password>\n";
              break;
            }
            if (isset($this->config[$user])) {
              $output .= "[INFO] His account already exists!\n";
              break;
            }
            $hashpass            = sha1($password);
            $this->config[$user] = array('password' => $hashpass);
            $this->api->plugin->writeYAML($this->path . "config.yml", $this->config);
            $output .= "[INFO] " . $user . "'s registration is successful!\n";
            break;
          case "login":
          case "logout":
            $output .= "[INFO] This command is only for the players.\n";
            break;
          case "resident":
            $output .= "[INFO] Resident list:\n";
            foreach ($this->config as $name => $data) {
              $online = $this->api->player->get($name);
              if ($online) {
                $online = "\x1b[32m[ONLINE]\x1b[0m ";
              } else {
                $online = "\x1b[31;1m[OFFLINE]\x1b[0m";
              }
              $last = "\x1b[36m[LASTJOIN:" . date("Y/m/d H:i:s", $data['last']) . "]\x1b[0m ";
              $output .= $online . $last . $name . "\n";
            }
            break;
          case "unregister":
            $username = array_shift($params);
            if (empty($username)) {
              $output .= "Usage: /rmaccount <player>\n";
              break;
            }
            $user = $this->api->player->get($username);
            if (!isset($this->config[$username])) {
              $output .= "[INFO] " . $username . " is not the resident!\n";
              break;
            }
            unset($this->config[$username]);
            $this->api->plugin->writeYAML($this->path . "config.yml", $this->config);
            $this->login[$user] = false;
            if ($user) {
              $user->sendChat("[INFO] Your account was removed!\n");
            }
            $output .= "[INFO] " . $username . "'s account was removed!\n";
            break;
          case "changepassword":
            $user     = array_shift($params);
            $password = array_shift($params);
            if (empty($password)) {
              $output .= "[INFO] Usage: /changepassword <player> <new password>\n";
              break;
            }
            $this->config[$user]['password'] = sha1($password);
            $this->api->plugin->writeYAML($this->path . "config.yml", $this->config);
            $output .= "[INFO] " . $user . "'s password is changed to \"" . $password . "\"\n";
            break;
          case "purge":
            $day = (int)array_shift($params);
            if (empty($day)) {
              $output .= "[INFO] Usage: /purge <days>\n";
              break;
            }
            $days  = $day * 86400;
            $users = "";
            foreach ($this->config as $user => $data) {
              if (empty($data['last'])) {
                continue;
              }
              if (time() - $data['last'] >= $days) {
                unset($this->config[$user]);
                $users .= $user . " ";
              }
            }
            $this->api->plugin->writeYAML($this->path . "config.yml", $this->config);
            $output .= "[INFO] Removed these account: " . $users . "\n";
            break;
        }
      }
      return $output;
    }

    public function createConfig()
    {
      $default      = array();
      $this->path   = $this->api->plugin->createConfig($this, $default);
      $this->config = $this->api->plugin->readYAML($this->path . "config.yml");
    }
  }
