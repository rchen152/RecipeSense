<?php
  require("../includes/config.php");

  if (isset($_POST["recipe"]) && isset($_POST["recipe"]["id"])) {

    /******** UPDATE RECIPE PROFILE ********/

    $query_start = "UPDATE recipe SET ";
    $query_str = $query_start;
    $parameters = array();

    // Name
    if (isset($_POST["recipe"]["name"])) {
      $query_str .= "name = ?, ";
      $parameters[] = $_POST["recipe"]["name"];
    }

    // Prep time
    if (isset($_POST["recipe"]["prep_time"])) {
      $prep = explode(" ", $_POST["recipe"]["prep_time"]);
      $prep_time = $prep[0];
      $prep_time_unit_id = get_unit_id($prep[1], "time");
      if ($prep_time_unit_id < 0) {
        echo json_encode("unrecognized time unit: ".$prep[1]);
        return;
      }
      $query_str .= "prep_time = ?, prep_time_unit_id = ?, ";
      array_push($parameters, $prep_time, $prep_time_unit_id);
    }

    // Prep time active
    if (isset($_POST["recipe"]["prep_time_active"])) {
      if (!empty($_POST["recipe"]["prep_time_active"])) {
        $prep_active = explode(" ", $_POST["recipe"]["prep_time_active"]);
        $prep_time_active = $prep_active[0];
        $prep_time_active_unit_id = get_unit_id($prep_active[1], "time");
        if ($prep_time_active_unit_id < 0) {
          echo json_encode("unrecognized active time unit: ".$prep_active[1]);
          return;
        }
        $query_str .= "prep_time_active = ?, prep_time_active_unit_id = ?, ";
        array_push($parameters, $prep_time_active, $prep_time_active_unit_id);
      } else {
        $query_str .=
          "prep_time_active = NULL, prep_time_active_unit_id = NULL, ";
      }
    }

    // Serving number
    if (isset($_POST["recipe"]["serving_number"])) {
      $query_str .= "serving_number = ?, ";
      $parameters[] = $_POST["recipe"]["serving_number"];
    }

    // Serving note
    if (isset($_POST["recipe"]["serving_note"])) {
      if (!empty($_POST["recipe"]["serving_note"])) {
        $query_str .= "serving_note = ?, ";
        $parameters[] = $_POST["recipe"]["serving_note"];
      } else {
        $query_str .= "serving_note = NULL, ";
      }
    }

    // Calories
    if (isset($_POST["recipe"]["calories"])) {
      $query_str .= "calories = ?, ";
      $parameters[] = $_POST["recipe"]["calories"];
    }

    // Execute query
    if (strlen($query_str) > strlen($query_start)) {
      $query_str = substr($query_str, 0, -2)." WHERE id = ?";
      $parameters[] = $_POST["recipe"]["id"];
      if (query($query_str, $parameters) === false) {
        echo json_encode("profile update failure");
        return;
      }
      $query_str = "";
      $parameters = array();
    }

    /******** UPDATE RECIPE INGREDIENTS ********/

    if (isset($_POST["recipe"]["ingredients"])) {
      $query_del = "DELETE FROM recipe_ingredient WHERE recipe_id = ?";
      $param_del = [$_POST["recipe"]["id"]];

      // If no ingredients, delete all
      if (!is_array($_POST["recipe"]["ingredients"])) {
        if (query($query_del, $param_del) === false) {
          echo json_encode("ingredient delete failure");
          return;
        }
      } else {
        $query_str = "INSERT INTO recipe_ingredient ".
          "(quantity, unit_id, ingredient_id, ".
          "recipe_ingredient_group_id, recipe_id) VALUES ";
        foreach ($_POST["recipe"]["ingredients"] as $ingredient) {
          $query_str .= "(?, ";
          $description_arr = explode(" ", $ingredient["description"]);

          // Quantity + unit + name
          $name = implode(" ", array_slice($description_arr, 1));
          $unit_id = get_unit_id($description_arr[0], "ingredient");
          $id = get_ingredient_id($name);

          // Quantity + name
          if ($unit_id < 0 || $id < 0) {
            $id = get_ingredient_id($ingredient["description"]);
          }
          if ($id < 0) {
            echo json_encode(
              "unrecognized ingredient: ".$ingredient["description"]);
            return;
          }

          $group_id = get_recipe_ingredient_group_id($ingredient["group_name"]);
          if ($group_id < 0) {
            echo json_encode(
              "bad ingredient group: ".$ingredient["group_name"]);
          }

          // Quantity
          $parameters[] = $ingredient["quantity"];

          // Unit id
          if ($unit_id >= 0) {
            $query_str .= "?, ";
            $parameters[] = $unit_id;
          } else {
            $query_str .= "NULL, ";
          }

          // Ingredient id, recipe ingredient group id, recipe id
          $query_str .= "?, ?, ?), ";
          array_push($parameters, $id, $group_id, $_POST["recipe"]["id"]);
        }

        // Delete old ingredients
        if (query($query_del, $param_del) === false) {
          echo json_encode("ingredient delete failure");
          return;
        }

        // Execute query
        $query_str = substr($query_str, 0, -2);
        if (query($query_str, $parameters) === false) {
          echo json_encode("ingredient update failure");
          return;
        }
        $query_str = "";
        $parameters = array();
      }
    }

    /******** UPDATE RECIPE INSTRUCTIONS ********/

    if (isset($_POST["recipe"]["instructions"])) {
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

    // Return updated profile
    echo json_encode(get_recipe_profile($_POST["recipe"]["id"])[0]);
  }
?>