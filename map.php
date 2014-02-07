<?php
  $imgpath = "map.png";
  $svgpath = "map.svg";
  ob_start();
  set_time_limit(0);
  require_once("Spyc.php");
  $file     = "config.yml";
  $Textures = "Textures/";
  $yaml     = file_get_contents($file);
  $parsed   = Spyc::YAMLLoad($yaml);
  if (!isset($_REQUEST['size']))
    $size = 8;
  else
    $size = $_REQUEST['size'];
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
  <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" version="1.1" width="<?php echo  $size * 256 ?>" height="<?php echo  $size * 256 ?>">
    <defs><?php foreach ($names as $name) {
      ?>
    <pattern id="<?php echo  $name ?>" patternUnits="userSpaceOnUse" x="0" y="0" width="<?php echo  $size ?>" height="<?php echo  $size ?>" viewBox="0 0 <?php echo  $size ?> <?php echo  $size ?>">
      <image xlink:href="<?php echo  $Textures . $name ?>.png" height="<?php echo  $size ?>" width="<?php echo  $size ?>"/></pattern><?php } ?></defs><?php
    foreach ($parsed as $x => $row) {
      foreach ($row as $y => $col) {
        ?><rect x="<?php echo $size * $x; ?>" y="<?php echo $size * $y; ?>" width="<?php echo  $size ?>" height="<?php echo  $size ?>" fill="url(#<?php echo @$names[$col] ?>)" /><?php
      }
    }
    ?></svg><?php
    $svg = ob_get_contents();
  file_put_contents($svgpath, $svg);
  $im = new Imagick($svgpath);
  //$im->readImageBlob($svg);
  $im->setImageFormat("png24");
  $im->writeImage($imgpath);
  $im->clear();
  $im->destroy();
  ob_clear();
