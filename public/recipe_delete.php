<?php
  require("../includes/config.php");

  if (isset($_POST["recipe_id"])) {
    $queries = array(
      "instruction" => "DELETE FROM recipe_instruction WHERE recipe_id = ?",
      "ingredient" => "DELETE FROM recipe_ingredient WHERE recipe_id = ?",
      "profile" => "DELETE FROM recipe WHERE id = ?"
    );
    foreach (array_keys($queries) as $query_key) {
      if (query($queries[$query_key], [$_POST["recipe_id"]]) === false) {
        echo json_encode($query_key . " delete failure");
        return;
      }
    }
    echo json_encode($_POST["recipe_id"]);
  }
?>