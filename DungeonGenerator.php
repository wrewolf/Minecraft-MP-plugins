<?php
  /*
__PocketMine Plugin__
name=DungeonGenerator
description=WreWolf DungeonGenerator based on original DungeonGenerator by Omattyao
version=0.5
author=WreWolf
class=DungeonGenerator
apiversion=9
*/

  class DungeonGenerator implements Plugin
  {
    private $api, $fence, $fork, $count, $step, $cmax, $min, $max;

    public function __construct(ServerAPI $api, $server = false)
    {
      $this->api   = $api;
      $this->fence = (int)0;
      $this->cfork = (int)0;
      $this->step  = 1;
    }

    public function init()
    {
      $this->api->console->register("generate", "Build a dungeon.", array($this, "commandH"));
    }

    public function __destruct()
    {
    }

    public function commandH($cmd, $params, $issuer, $alias)
    {
      $output = "";
      switch ($cmd) {
        case 'generate':
          if ($issuer !== "console") {
            $output .= "Must be run on the console.\n";
            break;
          }
          $dungeon = strtolower(implode("", $params));
          switch ($dungeon) {
            case '1':
            case 'shaft':
            case 'mineshaft':
            case 'abandonedmineshaft':
              $this->cmax = 700;
              $this->abandonedMineShaft($output);
              break;
            default:
              $output .= "Usage: /generate <dungeon>\n";
              $output .= "DungeonList:\n";
              $output .= "\"1\": Abandoned mine shaft\n";
          }
      }
      return $output;
    }

    public function abandonedMineShaft(&$output)
    {
      console("Generating...(It takes about 1~5 minutes)");
      $this->fork = array();
      $crd        = $this->setCenter();
      if ($crd === false) {
        console("Failed to generate: couldn't set center point.");
      }
      $this->min = array(10, $crd[1] - 10, 10);
      $this->max = array(245, $crd[1] + 10, 245);
      console("Seted center point to (" . $crd[0] . ", " . $crd[1] . ", " . $crd[2] . ")");
      $this->gFork($crd[0], $crd[1], $crd[2]);
      console("Finished!! (" . $this->count . " forks)");
      $this->fork = array();
    }

    public function setCenter()
    {
      $this->showFunc(__FUNCTION__);
      $i = (int)0;
      while (true) {
        ++$i;
        $x      = mt_rand(10, 245);
        $y      = mt_rand(20, 40);
        $z      = mt_rand(10, 245);
        $blocks = $this->getCube($x, $y, $z);
        foreach ($blocks as $crd) {
          $block = $this->getBlock($crd[0], $crd[1], $crd[2])->getID();
          if (in_array($block, array(1, 3, 13, 14, 15, 16, 21, 56, 73))) {
            continue;
          } else {
            $no = true;
          }
        }
        if (!$no) {
          break;
        } elseif ($i > 100) {
          return false;
        }
      }
      return array($x, $y, $z);
    }

    public function getBlock($x, $y, $z)
    {
      return $this->api->level->getDefault()->getBlock(new Vector3($x, $y, $z));
    }

    public function setBlock($vector3, $block)
    {
      $this->api->level->getDefault()->setBlock($vector3, $block);
    }

    public function gRoad($x, $y, $z, $direction)
    {
      $this->showFunc(__FUNCTION__);
      $this->digCube($x, $y, $z);
      switch ($direction) {
        case "0":
          $xsupp = 0;
          $zsupp = 3;
          break;
        case "1":
          $xsupp = -3;
          $zsupp = 0;
          break;
        case "2":
          $xsupp = 0;
          $zsupp = -3;
          break;
        case "3":
          $xsupp = 3;
          $zsupp = 0;
          break;
        default:
          return false;
      }
      $this->putCobweb($x, $y, $z);
      $this->checkFence($x, $y, $z, $direction);
      $a = $this->isUnderground($x + $xsupp, $y, $z + $zsupp);
      $b = $this->isUnderground($x + $xsupp * 2, $y, $z + $zsupp * 2);
      $c = $this->inRange($x + $xsupp, $y, $z + $zsupp);
      if ($a and $b and $c) {
        if ($this->probability(70)) {
          if ($this->probability(5)) {
            $ysupp      = $this->step;
            $this->step = $this->step * -1;
          } else {
            $ysupp = 0;
          }
          $this->gRoad($x + $xsupp, $y + $ysupp, $z + $zsupp, $direction);
        } elseif ($this->probability(90)) {
          $this->gFork($x + $xsupp, $y, $z + $zsupp, $direction);
        }
      }
    }

    public function gFork($x, $y, $z, $direction = 10)
    {
      $this->showFunc(__FUNCTION__);
      ++$this->count;
      if ($this->count > $this->cmax) {
        return false;
      }
      $this->fork[] = array($x, $y, $z);
      $this->digCube($x, $y, $z);
      $this->putCobweb($x, $y, $z);
      $prob       = (int)50;
      $directions = array(0, 1, 2, 3);
      shuffle($directions);
      foreach ($directions as $dir) {
        switch ($dir) {
          case "0":
            $xsupp = 0;
            $zsupp = 3;
            break;
          case "1":
            $xsupp = -3;
            $zsupp = 0;
            break;
          case "2":
            $xsupp = 0;
            $zsupp = -3;
            break;
          case "3":
            $xsupp = 3;
            $zsupp = 0;
            break;
          default:
            return false;
        }
        $a = $this->isUnderground($x + $xsupp, $y, $z + $zsupp);
        $b = $this->isUnderground($x + $xsupp * 2, $y, $z + $zsupp * 2);
        $c = $this->inRange($x + $xsupp, $y, $z + $zsupp);
        if ($a and $b and $c) {
          if ($this->probability($prob)) {
            $this->gRoad($x + $xsupp, $y, $z + $zsupp, $dir);
          } elseif ($this->probability(3)) {
            $this->checkStairs($x + $xsupp, $y, $z + $zsupp, $dir);
          } else {
            $prob += 5;
          }
        } else {
          $prob += 5;
        }
      }
    }

    public function gStairs($x, $y, $z, $direction)
    {
      $this->showFunc("\x1b[32m" . __FUNCTION__ . "\x1b[0m");
      $this->digStairs($x, $y, $z, $direction);
      switch ($direction) {
        case "0":
          $xsupp = 0;
          $zsupp = 3;
          break;
        case "1":
          $xsupp = -3;
          $zsupp = 0;
          break;
        case "2":
          $xsupp = 0;
          $zsupp = -3;
          break;
        case "3":
          $xsupp = 3;
          $zsupp = 0;
          break;
        default:
          return false;
      }
      $this->gRoad($x + $xsupp, $y - 1, $z + $zsupp, $direction);
    }

    public function checkStairs($x, $y, $z, $direction)
    {
      $this->showFunc(__FUNCTION__);
      switch ($direction) {
        case "0":
          $xsupp = 0;
          $zsupp = 3;
          break;
        case "1":
          $xsupp = -3;
          $zsupp = 0;
          break;
        case "2":
          $xsupp = 0;
          $zsupp = -3;
          break;
        case "3":
          $xsupp = 3;
          $zsupp = 0;
          break;
        default:
          return false;
      }
      $a = $this->isUnderground($x, $y - 3, $z);
      $b = $this->isUnderground($x + $xsupp, $y - 3, $z + $zsupp);
      $c = $this->isUnderground($x + $xsupp * 2, $y - 3, $z + $zsupp * 2);
      $d = $this->inRange($x + $xsupp, $y - 3, $z + $zsupp);
      if ($a and $b and $c and $d) {
        $this->digCube($x, $y, $z);
        $this->gStairs($x, $y - 3, $z, $direction);
      }
    }

    public function isUnderground($x, $y, $z)
    {
      $this->showFunc(__FUNCTION__);
      $blocks = $this->getCube($x, $y, $z);
      $ret    = true;
      if ($this->probability(75)) {
        $earth = array(0, 1, 3, 13, 14, 15, 16, 21, 30, 56, 73);
      } else {
        $earth = array(1, 3, 13, 14, 15, 16, 21, 30, 56, 73);
      }
      foreach ($blocks as $crd) {
        $block = $this->getBlock($crd[0], $crd[1], $crd[2])->getID();
        if (!in_array($block, $earth)) {
          $ret = false;
          break;
        }
      }
      return $ret;
    }

    public function inRange($x, $y, $z)
    {
      $this->showFunc(__FUNCTION__);
      if ($this->min[0] < $x and $x < $this->max[0]) {
        if ($this->min[1] < $y and $y < $this->max[1]) {
          if ($this->min[2] < $z and $z < $this->max[2]) {
            return true;
          }
        }
      }
      return false;
    }

    public function digCube($x, $y, $z)
    {
      $this->showFunc(__FUNCTION__);
      $blocks = $this->getCube($x, $y, $z);
      foreach ($blocks as $crd) {
        if ($crd[1] == ($y + 1) and $this->probability(7)) {
          continue;
        }
        $this->setBlock(new Vector3($crd[0], $crd[1], $crd[2]), new AirBlock());
      }
    }

    public function digStairs($x, $y, $z, $direction)
    {
      $this->showFunc(__FUNCTION__);
      $blocks = array(array(0, 0, 0), array(1, 0, 0), array(1, 1, 0), array(0, 1, 0), array(-1, 1, 0), array(1, -1, 0), array(0, 0, 1), array(1, 0, 1), array(1, 1, 1), array(0, 1, 1), array(-1, 1, 1), array(1, -1, 1), array(0, 0, -1), array(1, 0, -1), array(1, 1, -1), array(0, 1, -1), array(-1, 1, -1), array(1, -1, -1));
      switch ($direction) {
        case "0":
          foreach ($blocks as $crd) {
            $this->setBlock(new Vector3($x + $crd[2], $y + $crd[1], $z + $crd[0]), new AirBlock());
          }
          break;
        case "1":
          foreach ($blocks as $crd) {
            $this->setBlock(new Vector3($x + $crd[0] * -1, $y + $crd[1], $z + $crd[2]), new AirBlock());
          }
          break;
        case "2":
          foreach ($blocks as $crd) {
            $this->setBlock(new Vector3($x + $crd[2], $y + $crd[1], $z + $crd[0] * -1), new AirBlock());
          }
          break;
        case "3":
          foreach ($blocks as $crd) {
            $this->setBlock(new Vector3($x + $crd[0], $y + $crd[1], $z + $crd[2]), new AirBlock());
          }
          break;
        default:
          return false;
      }
    }

    public function coatingFloor($x, $y, $z)
    {
      $this->showFunc(__FUNCTION__);
      $blocks = array(array($x, $y + 2, $z), array($x + 1, $y + 2, $z), array($x + 1, $y + 2, $z + 1), array($x, $y + 2, $z + 1), array($x - 1, $y + 2, $z + 1), array($x - 1, $y + 2, $z), array($x - 1, $y + 2, $z - 1), array($x, $y + 2, $z - 1), array($x + 1, $y + 2, $z - 1), array($x, $y - 2, $z), array($x + 1, $y - 2, $z), array($x + 1, $y - 2, $z + 1), array($x, $y - 2, $z + 1), array($x - 1, $y - 2, $z + 1), array($x - 1, $y - 2, $z), array($x - 1, $y - 2, $z - 1), array($x, $y - 2, $z - 1), array($x + 1, $y - 2, $z - 1));
      foreach ($blocks as $crd) {
        $block = $this->getBlock($crd[0], $crd[1], $crd[2])->getID();
        if ($block == 3) {
          $this->setBlock(new Vector3($crd[0], $crd[1], $crd[2]), new StoneBlock());
        }
      }
    }

    public function coatingWall($x, $y, $z, $direction)
    {
      $this->showFunc(__FUNCTION__);
      $blocks = $this->getWall($x, $y, $z, $direction);
      foreach ($blocks as $crd) {
        $block = $this->getBlock($crd[0], $crd[1], $crd[2])->getID();
        if ($block == 3) {
          $this->setBlock(new Vector3($crd[0], $crd[1], $crd[2]), new StoneBlock());
        }
      }
    }

    public function checkFence($x, $y, $z, $direction)
    {
      $this->showFunc(__FUNCTION__);
      switch ($direction) {
        case "0":
          $blocks = array(array($x, $y, $z - 1), array($x, $y, $z), array($x, $y, $z + 1));
          break;
        case "1":
          $blocks = array(array($x + 1, $y, $z), array($x, $y, $z), array($x - 1, $y, $z));
          break;
        case "2":
          $blocks = array(array($x, $y, $z + 1), array($x, $y, $z), array($x, $y, $z - 1));
          break;
        case "3":
          $blocks = array(array($x - 1, $y, $z), array($x, $y, $z), array($x + 1, $y, $z));
          break;
        default:
          return false;
      }
      foreach ($blocks as $crd) {
        ++$this->fence;
        if ((($this->fence + 4) % 6) == 0) {
          $this->putFence($crd[0], $crd[1], $crd[2], $direction);
        }
      }
    }

    public function putCobweb($x, $y, $z, $direction)
    {
      $this->showFunc(__FUNCTION__);
      $blocks = array(array($x, $y + 1, $z), array($x + 1, $y + 1, $z), array($x + 1, $y + 1, $z + 1), array($x, $y + 1, $z + 1), array($x - 1, $y + 1, $z + 1), array($x - 1, $y + 1, $z), array($x - 1, $y + 1, $z - 1), array($x, $y + 1, $z - 1), array($x + 1, $y + 1, $z - 1));
      foreach ($blocks as $crd) {
        if ($this->probability(5)) {
          $this->setBlock(new Vector3($crd[0], $crd[1], $crd[2]), new CobwebBlock());
        }
      }
    }

    public function putFence($x, $y, $z, $direction)
    {
      $this->showFunc(__FUNCTION__);
      switch ($direction) {
        case "0":
        case "2":
          $blocks = array(array($x + 1, $y, $z, 85), array($x + 1, $y + 1, $z, 5), array($x - 1, $y + 1, $z, 5), array($x - 1, $y, $z, 85), array($x - 1, $y - 1, $z, 85), array($x + 1, $y - 1, $z, 85));
          break;
        case "1":
        case "3":
          $blocks = array(array($x, $y, $z - 1, 85), array($x, $y + 1, $z - 1, 5), array($x, $y + 1, $z + 1, 5), array($x, $y, $z + 1, 85), array($x, $y - 1, $z + 1, 85), array($x, $y - 1, $z - 1, 85));
          break;
        default:
          return false;
      }
      if ($this->probability(92)) {
        $blocks[] = array($x, $y + 1, $z, 5);
      }
      foreach ($blocks as $crd) {
        switch ($crd[3]) {
          case 85:
            $this->setBlock(new Vector3($crd[0], $crd[1], $crd[2]), new FenceBlock());
            break;
          case 5:
            $this->setBlock(new Vector3($crd[0], $crd[1], $crd[2]), new PlanksBlock());
            break;
        }
      }
    }

    public function getCube($x, $y, $z)
    {
      $this->showFunc(__FUNCTION__);
      $blocks = array(array($x, $y, $z), array($x + 1, $y, $z), array($x + 1, $y, $z + 1), array($x, $y, $z + 1), array($x - 1, $y, $z + 1), array($x - 1, $y, $z), array($x - 1, $y, $z - 1), array($x, $y, $z - 1), array($x + 1, $y, $z - 1), array($x, $y + 1, $z), array($x + 1, $y + 1, $z), array($x + 1, $y + 1, $z + 1), array($x, $y + 1, $z + 1), array($x - 1, $y + 1, $z + 1), array($x - 1, $y + 1, $z), array($x - 1, $y + 1, $z - 1), array($x, $y + 1, $z - 1), array($x + 1, $y + 1, $z - 1), array($x, $y - 1, $z), array($x + 1, $y - 1, $z), array($x + 1, $y - 1, $z + 1), array($x, $y - 1, $z + 1), array($x - 1, $y - 1, $z + 1), array($x - 1, $y - 1, $z), array($x - 1, $y - 1, $z - 1), array($x, $y - 1, $z - 1), array($x + 1, $y - 1, $z - 1));
      return $blocks;
    }

    public function getWall($x, $y, $z, $direction)
    {
      $this->showFunc(__FUNCTION__);
      switch ($direction) {
        case "0":
          $blocks = array(array($x, $y, $z + 2), array($x + 1, $y, $z + 2), array($x + 1, $y + 1, $z + 2), array($x, $y + 1, $z + 2), array($x - 1, $y + 1, $z + 2), array($x - 1, $y, $z + 2), array($x - 1, $y - 1, $z + 2), array($x, $y - 1, $z + 2), array($x + 1, $y - 1, $z + 2));
          break;
        case "1":
          $blocks = array(array($x - 2, $y, $z), array($x - 2, $y, $z - 1), array($x - 2, $y + 1, $z - 1), array($x - 2, $y + 1, $z), array($x - 2, $y + 1, $z + 1), array($x - 2, $y, $z + 1), array($x - 2, $y - 1, $z + 1), array($x - 2, $y - 1, $z), array($x - 2, $y - 1, $z - 1));
          break;
        case "2":
          $blocks = array(array($x, $y, $z - 2), array($x + 1, $y, $z - 2), array($x + 1, $y + 1, $z - 2), array($x, $y + 1, $z - 2), array($x - 1, $y + 1, $z - 2), array($x - 1, $y, $z - 2), array($x - 1, $y - 1, $z - 2), array($x, $y - 1, $z - 2), array($x + 1, $y - 1, $z - 2));
          break;
        case "3":
          $blocks = array(array($x + 2, $y, $z), array($x + 2, $y, $z - 1), array($x + 2, $y + 1, $z - 1), array($x + 2, $y + 1, $z), array($x + 2, $y + 1, $z + 1), array($x + 2, $y, $z + 1), array($x + 2, $y - 1, $z + 1), array($x + 2, $y - 1, $z), array($x + 2, $y - 1, $z - 1));
          break;
        default:
          return false;
      }
      return $blocks;
    }

    public function probability($int = 100)
    {
      $this->showFunc(__FUNCTION__);
      return rand(1, 99) < $int;
    }

    public function showFunc($func)
    {
    }
  }