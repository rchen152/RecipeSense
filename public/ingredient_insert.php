<?php
  require("../includes/config.php");

  if (isset($_POST["ingredient"])) {
    $query_str = "INSERT INTO ingredient (name, plural) VALUES (?, ";
    $parameters = array($_POST["ingredient"]["name"]);
    if (isset($_POST["ingredient"]["plural"])) {
      $query_str .= "?";
      $parameters[] = $_POST["ingredient"]["plural"];
    } else {
      $query_str .= "NULL";
    }
    $query_str .= ") ON DUPLICATE KEY UPDATE id = LAST_INSERT_ID(id), ".
      "name = VALUES(name), plural = VALUES(plural)";
    if (query($query_str, $parameters) === false) {
      echo json_encode("ingredient insert failure");
      return;
    }

    $id = query("SELECT LAST_INSERT_ID()")[0]["LAST_INSERT_ID()"];
    foreach (array_keys($_SESSION["ingredients"]) as $ingredient) {
      if ($_SESSION["ingredients"][$ingredient] === $id) {
        unset($_SESSION["ingredients"][$ingredient]);
      }
    }
    $_SESSION["ingredients"][$_POST["ingredient"]["name"]] = $id;
    if (isset($_POST["ingredient"]["plural"])) {
      $_SESSION["ingredients"][$_POST["ingredient"]["plural"]] = $id;
    }

    echo json_encode("&check;");
  }
?>