<?php
  require("../includes/config.php");

  if (isset($_POST["prefix"])) {
    $matches = array();
    foreach (array_keys($_SESSION["ingredients"]) as $ingredient) {
      if (strpos($ingredient, $_POST["prefix"]) === 0) {
        $matches[] = $ingredient;
      }
    }
    echo json_encode($matches);
  }
?>