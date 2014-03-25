<?php

   /*
   __PocketMine Plugin__
   name=AdminInventory
   description=A new kind of inventory administration!
   version=0.1
   author=wrewolf
   class=AdminInventory
   apiversion=13
   */


   class AdminInventory implements Plugin
   {
      private $api, $path;

      public function __construct(ServerAPI $api, $server = false)
      {
         $this->api = $api;
      }

      public function init()
      {
         $this->api->console->register("clearinventory", "Clean inventory a user", array($this, "clearinventory"));
         $this->api->console->register("invsee", "See inventory a user", array($this, "invsee"));
      }

      public function __destruct()
      {
      }

      public function clearinventory($cmd, $params, $issuer, $alias, $args, $issuer)
      {
         $username = $params[0];
         if($username === '' && $username === null)
            return "usage clearinventory username";

         $player = $this->api->player->get($username);
         if(! ($player instanceof Player) and ! ($player->entity instanceof Entity)) {
            return "player not found";
         }
         $player->inventory = array();
         $player->sendInventory();
         return "Inventory cleaned";
      }

      public function invsee($cmd, $params, $issuer, $alias, $args, $issuer)
      {
         $username = $params[0];
         if($username === '' && $username === null)
            return "usage invsee username";
         $player = $this->api->player->get($username);
         if(! ($player instanceof Player) and ! ($player->entity instanceof Entity)) {
            return "player not found";
         }
         $inv = array();
         foreach($player->inventory as $slot => $item) {
            if($item->getID() !== 0)
               $inv[] = $item->getID() . ":" . $item->getName() . ":" . $item->getMetadata() . " " . $item->count;
         }
         if(!count($inv))
            return "Inv clean";
         return implode(", ", $inv);
      }
   }
