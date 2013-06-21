<?php
  if (!file_exists("map.png")) {
    ob_start();
    set_time_limit(0);
    require_once("Spyc.php");
    $file   = "config.yml";
    $yaml   = file_get_contents($file);
    $parsed = Spyc::YAMLLoad($yaml);
    if (!isset($_GET['size']))
      $size = 2;
    else
      $size = $_GET['size'];
    $names = array(
      1   => "stone",
      2   => "grass",
      3   => "dirt",
      4   => "cobblestone",
      5   => "woodenplank",
      6   => "sapling",
      7   => "bedrock",
      8   => "water",
      9   => "stationarywater",
      12  => "sand",
      13  => "gravel",
      15  => "ironore",
      16  => "coalore",
      17  => "wood",
      18  => "leaves",
      20  => "glass",
      24  => "sandstone",
      31  => "tallgrass",
      35  => "wool",
      37  => "yellowflower",
      38  => "cyanflower",
      43  => "DOUBLE_SLABS",
      44  => "stoneslab",
      45  => "brick",
      46  => "tnt",
      48  => "mossstone",
      49  => "obsidian",
      50  => "torch",
      53  => "woodenstairs",
      54  => "CHEST",
      58  => "workbench",
      59  => "wheat",
      60  => "farmland",
      61  => "furnace",
      63  => "SIGN_POST",
      65  => "ladder",
      67  => "cobblestonestairs",
      68  => "WALL_SIGN",
      78  => "snow",
      79  => "ice",
      83  => "sugarcane",
      85  => "fence",
      89  => "glowstone",
      96  => "trapdoor",
      98  => "stonebricks",
      102 => "glasspane",
      107 => "fencegate",
      109 => "stonebrickstairs",
      128 => "sandstonestairs"
    );
    ?>
    <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" version="1.1" width="<?= $size * 256 ?>" height="<?= $size * 256 ?>">
    <defs><?php foreach ($names as $name) {
      if (!file_exists("Textures/$name.png"))
        die("Textures/$name.png");
      ?>
      <pattern id="<?= $name ?>" patternUnits="userSpaceOnUse" x="0" y="0" width="<?= $size ?>" height="<?= $size ?>" viewBox="0 0 <?= $size ?> <?= $size ?>">
      <image xlink:href="Textures/<?= $name ?>.png" height="<?= $size ?>" width="<?= $size ?>"/></pattern><?php } ?></defs><?php
    foreach ($parsed as $x => $row) {
      foreach ($row as $y => $col) {
        ?>
        <rect x="<?php echo $size * $y; ?>" y="<?php echo $size * $x; ?>" width="<?= $size ?>" height="<?= $size ?>" fill="url(#<?php echo $names[$col] ?>)" /><?php
      }
    }
    ?></svg><?php
    $svg = ob_get_contents();
    $im  = new Imagick();
    $im->readImageBlob($svg);
    $im->setImageFormat("png24");
    $im->writeImage('map.png');
    $im->clear();
    $im->destroy();
    ob_clear();
  }
?>
<html>
<body>
<img src="map.png">
</body>
</html>