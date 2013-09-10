<?php

  /*
   __PocketMine Plugin__
  name=PocketJobs
  description=PocketJobs introduces a job system into your PocketMine world.
  version=1.2
  author=MinecrafterJPN
  class=PocketJobs
  apiversion=9
  */

  class PocketJobs implements Plugin
  {
    private $api, $path, $jobList = array(),$Playerconfig;

    public function __construct(ServerAPI $api, $server = false)
    {
      $this->api = $api;
    }

    public function init()
    {
      $this->path = $this->api->plugin->createConfig($this, array());
      if (filesize($this->path . "config.yml") <= 4) {
        $this->defaultConfig();
      }
      $this->jobList = $this->api->plugin->readYAML($this->path . "config.yml");
      touch($this->path . "playerlist.yml");
      if (filesize($this->path . "playerlist.yml") === 0) {
        $this->api->plugin->writeYAML($this->path . "playerlist.yml", array("slot1" => "null", "slot2" => "null"));
      }
      $this->Playerconfig= $this->api->plugin->readYAML($this->path . "playerlist.yml");

      $this->api->console->register("jobs", "Super command of PocketJobs", array($this, "jobs_commandHandler"));
      $this->api->ban->cmdWhitelist("jobs");
      $this->api->addHandler("player.block.break", array($this, "break_eventHandler"));
      $this->api->addHandler("player.block.place", array($this, "place_eventHandler"));
    }

    public function __destruct()
    {
    }

    public function break_eventHandler($data, $event)
    {
      $this->workCheck("break", $data['player']->username, $data['target']->getID(), $data['target']->getMetadata());
    }

    public function place_eventHandler($data, $event)
    {
      $this->workCheck("place", $data['player']->username, $data['item']->getID(), $data['item']->getMetadata());
    }

    public function jobs_commandHandler($cmd, $args, $issuer, $alias)
    {
      $output = "";
      $subCmd = strtolower($args[0]);
      switch ($subCmd) {
        case "":
          $output .= "[PocketJobs]/jobs : show the commands available to you\n";
          $output .= "[PocketJobs]/jobs browse : browse the jobs available to you\n";
          $output .= "[PocketJobs]/jobs join <jobname> : join the selected job\n";
          $output .= "[PocketJobs]/jobs leave <jobname> : leave the selected job\n";
          $output .= "[PocketJobs]/jobs info <jobname> : show the detail of selected job\n";
          $output .= "[PocketJobs]/jobs reset : reset the job list to default\n";
          break;
        case "ls":
        case "list":
        case "browse":
          $output .= "[PocketJobs]";
          foreach ($this->jobList as $job) {
            $output .= $job['jobname'] . " ";
          }
          $output .= "\n";
          break;
        case "join":
          if ($issuer === "console") {
            console("[PocketJobs]Must be run on the world.");
            break;
          }
          if (!isset($args[1])) {
            $output .= "Usage: /jobs join <jobname>\n";
            break;
          }
          $jobname  = strtolower($args[1]);
          $jobExist = false;
          foreach ($this->jobList as $job) {
            if (strtolower($job['jobname']) === $jobname) {
              $jobExist = true;
            }
          }
          if (!$jobExist) {
            $output .= "[PocketJobs]$args[1] not found.";
            break;
          }
          $output .= $this->joinJob($issuer->username, $jobname);
          break;
        case "leave":
          if ($issuer === "console") {
            console("[PocketJobs]Must be run on the world.");
            break;
          }
          if (!isset($args[1])) {
            $output .= "Usage: /jobs leave <jobname>\n";
            break;
          }
          $jobname  = strtolower($args[1]);
          $jobExist = false;
          foreach ($this->jobList as $job) {
            if (strtolower($job['jobname']) === $jobname) {
              $jobExist = true;
            }
          }
          if (!$jobExist) {
            $output .= "[PocketJobs]$args[1] not found.";
            break;
          }
          $output .= $this->leaveJob($issuer->username, $jobname);
          break;
        case "info":
          if (isset($args[1]) === false) {
            $output .= "Usage: /jobs info <jobname>\n";
            break;
          }
          $jobname  = strtolower($args[1]);
          $jobExist = false;
          foreach ($this->jobList as $job) {
            if (strtolower($job['jobname']) === $jobname) {
              $jobExist = true;
            }
          }
          if (!$jobExist) {
            $output .= "[PocketJobs]$args[1] not found.";
            break;
          }
          $output .= $this->infoJob($jobname);
          break;
        case "stat":
          if ($issuer === "console") {
            console("[PocketJobs]Must be run on the world.");
            break;
          }
          $output .= "[PocketJobs]Unimplemented wait for a moment :)";
          break;
        case "reset":
          if ($issuer !== "console") {
            $output .= "[PocketJobs]Must be run on the console.\n";
            break;
          }
          console("[PocketJobs]Reseting the config...");
          $this->defaultConfig();
          console("[PocketJobs]Completed");
          break;
      }
      return $output;
    }

    private function joinJob($username, $job)
    {
      if (isset($this->Playerconfig[$username]['slot1'])) {
        if (isset($this->Playerconfig[$username]['slot2'])) {
          return "[PocketJobs]Your job slot is full.\n";
        }
        $this->Playerconfig[$username]['slot2']= $job;
        $this->overwriteConfig2($this->Playerconfig, $this->path . "playerlist.yml");
        return "[PocketJobs]Set $job to your job slot2.\n";
      }
      $this->Playerconfig[$username]['slot1'] = $job;
      $this->Playerconfig[$username]['slot2'] = null;

      $this->overwriteConfig2($this->Playerconfig, $this->path . "playerlist.yml");
      return "[PocketJobs]Set $job to your job slot1.\n";
    }

    private function leaveJob($username, $job)
    {
      if (!array_key_exists($username, $this->Playerconfig)) return "[PocketJobs]You have no jobs.\n";
      if ($this->Playerconfig[$username]['slot1'] === $job) {
        $this->Playerconfig[$username]['slot1'] = null;
        $this->overwriteConfig2($this->Playerconfig, $this->path . "playerlist.yml");
        return "[PocketJobs]Remove $job from your job slot1.\n";
      } elseif ($this->Playerconfig[$username]['slot2'] === $job) {
        $this->Playerconfig[$username]['slot2'] = null;
        $this->overwriteConfig2($this->Playerconfig, $this->path . "playerlist.yml");
        return "[PocketJobs]Remove $job from your job slot2.\n";
      } else {
        return "[PocketJobs]You are not part of $job\n";
      }
    }

    private function infoJob($jobname)
    {
      $output  = "";
      $jobname = strtolower($jobname);
      foreach ($this->jobList as $job) {
        if (strtolower($job['jobname']) === $jobname) {
          $jn = $job['jobname'];
          $output .= "[PocketJobs]$jn \n";
          foreach ($job['salary'] as $type => $detail) {
            foreach ($detail as $value) {
              $id     = $value['ID'];
              $meta   = $value['meta'];
              $amount = $value['amount'];
              $output .= "[PocketJobs]$type $id:$meta $amount\n";
            }
          }
        }
      }
      return $output;
    }

    private function workCheck($type, $username, $id, $meta)
    {
      $flag         = false;
      if (array_key_exists($username, $this->Playerconfig) === false) {
        return;
      }
      foreach ($this->jobList as $job) {
        foreach ($job['salary'] as $jobType => $detail) {
          if ($jobType === $type) {
            foreach ($detail as $value) {
              if ($value['ID'] === $id and $value['meta'] === $meta) {
                $targetJob = strtolower($job['jobname']);
                $amount    = $value['amount'];
                $flag      = true;
              }
            }
          }
        }
      }
      if (!$flag) return;
      if ($this->Playerconfig[$username]['slot1'] === $targetJob or $this->Playerconfig[$username]['slot2'] === $targetJob) {
        $money = array(
          'username' => $username,
          'method'   => 'grant',
          'amount'   => $amount
        );
        $this->api->handle("money.handle", $money);
      }
    }

    private function defaultConfig()
    {
      $config = array(
        array(
          'jobname' => 'Woodcutter',
          'salary'  => array(
            'break' => array(
              array(
                'ID'     => 17,
                'meta'   => 0,
                'amount' => 25
              ),
              array(
                'ID'     => 17,
                'meta'   => 1,
                'amount' => 25
              ),
              array(
                'ID'     => 17,
                'meta'   => 2,
                'amount' => 25
              ),
              array(
                'ID'     => 17,
                'meta'   => 3,
                'amount' => 25
              ),
            ),
            'place' => array(
              array(
                'ID'     => 6,
                'meta'   => 0,
                'amount' => 1
              ),
              array(
                'ID'     => 6,
                'meta'   => 1,
                'amount' => 1
              ),
              array(
                'ID'     => 6,
                'meta'   => 2,
                'amount' => 1
              ),
              array(
                'ID'     => 6,
                'meta'   => 3,
                'amount' => 1
              )
            )
          )
        ),
        array(
          'jobname' => 'Miner',
          'salary'  => array(
            'break' => array(
              array(
                'ID'     => 1,
                'meta'   => 0,
                'amount' => 3
              ),
              array(
                'ID'     => 14,
                'meta'   => 0,
                'amount' => 25
              ),
              array(
                'ID'     => 15,
                'meta'   => 0,
                'amount' => 20
              ),
              array(
                'ID'     => 21,
                'meta'   => 0,
                'amount' => 17
              ),
              array(
                'ID'     => 49,
                'meta'   => 0,
                'amount' => 9
              ),
              array(
                'ID'     => 56,
                'meta'   => 0,
                'amount' => 80
              ),
              array(
                'ID'     => 73,
                'meta'   => 0,
                'amount' => 10
              )
            )
          )
        )
      );
      $this->api->plugin->writeYAML($this->path . "config.yml", $config);
    }
    private function overwriteConfig2($dat, $path)
    {
      $cfg    = $this->api->plugin->readYAML($path);
      $result = array_merge($cfg, $dat);
      $this->api->plugin->writeYAML($path, $result);
    }
  }