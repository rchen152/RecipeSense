<?php
  require("../includes/config.php");

  if (isset($_POST["recipe"]) && isset($_POST["recipe"]["id"])) {

    /******** UPDATE RECIPE INSTRUCTIONS ********/

    if (isset($_POST["recipe"]["instructions"])) {
      $parameters = array();

      if (!is_array($_POST["recipe"]["instructions"])) {
        $max_number = 0;
      } else {
        $max_number = count($_POST["recipe"]["instructions"]);
      }

      // Nonempty instruction list
      if ($max_number > 0) {
        $query_str = "INSERT INTO recipe_instruction ".
          "(number, show_number, description, recipe_id) VALUES ";
        foreach ($_POST["recipe"]["instructions"] as $instruction) {
          $query_str .= "(?, ?, ?, ?), ";
          array_push($parameters, $instruction["number"],
            $instruction["show_number"], $instruction["description"],
            $_POST["recipe"]["id"]);
        }
        $query_str = substr($query_str, 0, -2);
        $query_str .= " ON DUPLICATE KEY UPDATE ".
          "show_number = VALUES(show_number), ".
          "description = VALUES(description)";

        // Execute query
        if (query($query_str, $parameters) === false) {
          echo json_encode("instruction update failure");
          return;
        }
        $query_str = "";
        $parameters = array();
      }

      // Delete extra steps
      $query_str = "DELETE FROM recipe_instruction WHERE ".
        "number > ? AND recipe_id = ?";
      array_push($parameters, $max_number, $_POST["recipe"]["id"]);
      if (query($query_str, $parameters) === false) {
        echo json_encode("instruction delete failure");
        return;
      }
    }

    // Success
    echo json_encode("&check;");
  }
?>