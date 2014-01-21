<?php
  /*
__PocketMine Plugin__
name=DbConnector
description=DbConnector
version=2.0
author=WreWolf
class=DbConnector
apiversion=11
*/

  class DbConnector implements Plugin
  {
    private $api, $path, $server, $db;

    public function __construct(ServerAPI $api, $server = false)
    {
      $this->api    = $api;
      $this->server = ServerAPI::request();
    }

    public function init()
    {
      $this->path = $this->api->plugin->configPath($this);
      $this->api->addHandler("db.query.exec", array($this, "eventHandler"));
      $this->db = new mysqli('127.0.0.1', 'root', null);

      if ($this->db->connect_errno)
        die("Failed to connect to MySQL: (" . $this->db->connect_errno . ") " . $this->db->connect_errno);

      $this->db->query("CREATE DATABASE `m1` CHARACTER SET utf8 COLLATE utf8_general_ci;");
      $this->db->select_db("m1");

      $this->db->query("CREATE TABLE `main` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `timestamp` timestamp NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE CURRENT_TIMESTAMP,
  `tps` double DEFAULT NULL,
  `online` int(11) DEFAULT NULL,
  KEY `id` (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=187 DEFAULT CHARSET=utf8;");
      $this->db->query("CREATE TABLE `online` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `timestamp` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE CURRENT_TIMESTAMP,
  `user` varchar(255) NOT NULL,
  `cnt` int(11) NOT NULL,
  `level` varchar(60) NOT NULL,
  `x` double NOT NULL,
  `y` double NOT NULL,
  `z` double NOT NULL,
  UNIQUE KEY `user` (`user`) USING BTREE,
  KEY `id` (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;");
      $this->db->query("CREATE TABLE `antiGrif` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `timestamp` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE CURRENT_TIMESTAMP,
  `user` varchar(60) NOT NULL,
  `block` varchar(30) NOT NULL,
  `level` varchar(30) NOT NULL,
  `x` double NOT NULL,
  `y` double NOT NULL,
  `z` double NOT NULL,
  KEY `id` (`id`) USING BTREE,
  KEY `name` (`user`) USING BTREE,
  KEY `block` (`block`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8;");
      $this->db->query("SET NAMES 'UTF8'");
      $this->db->query("SET CHARACTER SET 'UTF8'");
    }

    public function eventHandler($data, $event)
    {
      console($data['query']);
      $this->db->multi_query($data['query']);
      if ($this->db->more_results())
        do {
          ;
        } while ($this->db->next_result());
    }

    public function __destruct()
    {
    }
  }
