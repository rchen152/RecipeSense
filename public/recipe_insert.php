<?php
  require("../includes/config.php");

  if (isset($_POST["recipe"])) {

    /******** INSERT RECIPE PROFILE ********/

    $query_str = "INSERT INTO recipe (";
    $parameters = array();

    // Name
    $query_str .= "name, ";
    $parameters[] = $_POST["recipe"]["name"];

    // Prep time
    $prep = explode(" ", $_POST["recipe"]["prep_time"]);
    $prep_time = $prep[0];
    $prep_time_unit_id = get_unit_id($prep[1], "time");
    if ($prep_time_unit_id < 0) {
      echo json_encode("unrecognized time unit: ".$prep[1]);
      return;
    }
    $query_str .= "prep_time, prep_time_unit_id, ";
    array_push($parameters, $prep_time, $prep_time_unit_id);

    // Prep time active
    if (isset($_POST["recipe"]["prep_time_active"]) &&
        !empty($_POST["recipe"]["prep_time_active"])) {
      $prep_active = explode(" ", $_POST["recipe"]["prep_time_active"]);
      $prep_time_active = $prep_active[0];
      $prep_time_active_unit_id = get_unit_id($prep_active[1], "time");
      if ($prep_time_active_unit_id < 0) {
        echo json_encode("unrecognized active time unit: ".$prep_active[1]);
        return;
      }
      $query_str .= "prep_time_active, prep_time_active_unit_id, ";
      array_push($parameters, $prep_time_active, $prep_time_active_unit_id);
    }

    // Serving number
    $query_str .= "serving_number, ";
    $parameters[] = $_POST["recipe"]["serving_number"];

    // Serving note
    if (isset($_POST["recipe"]["serving_note"]) &&
        !empty($_POST["recipe"]["serving_note"])) {
      $query_str .= "serving_note, ";
      $parameters[] = $_POST["recipe"]["serving_note"];
    }

    // Calories
    $query_str .= "calories, ";
    $parameters[] = $_POST["recipe"]["calories"];

    // Execute query
    $query_str = substr($query_str, 0, -2) . ") VALUES (";
    for ($i = 0, $n = count($parameters); $i < $n; ++$i) {
      $query_str .= "?, ";
    }
    $query_str = substr($query_str, 0, -2) . ")";
    if (query($query_str, $parameters) === false) {
      echo json_encode("profile insert failure");
      return;
    }
    $query_str = "";
    $parameters = array();
    $_POST["recipe"]["id"] =
      query("SELECT LAST_INSERT_ID()")[0]["LAST_INSERT_ID()"];

    /******** INSERT RECIPE INGREDIENTS ********/

    if (isset($_POST["recipe"]["ingredients"]) &&
        is_array($_POST["recipe"]["ingredients"])) {
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

      // Execute query
      $query_str = substr($query_str, 0, -2);
      if (query($query_str, $parameters) === false) {
        echo json_encode("ingredient insert failure");
        return;
      }
      $query_str = "";
      $parameters = array();
    }

    /******** INSERT RECIPE INSTRUCTIONS ********/

    if (isset($_POST["recipe"]["instructions"]) &&
        is_array($_POST["recipe"]["instructions"]) &&
        count($_POST["recipe"]["instructions"]) > 0) {
      $query_str = "INSERT INTO recipe_instruction ".
        "(number, show_number, description, recipe_id) VALUES ";
      foreach ($_POST["recipe"]["instructions"] as $instruction) {
        $query_str .= "(?, ?, ?, ?), ";
        array_push($parameters, $instruction["number"],
          $instruction["show_number"], $instruction["description"],
          $_POST["recipe"]["id"]);
      }
      $query_str = substr($query_str, 0, -2);

      // Execute query
      if (query($query_str, $parameters) === false) {
        echo json_encode("instruction insert failure");
        return;
      }
      $query_str = "";
      $parameters = array();
    }

    // Return updated profile
    echo json_encode(get_recipe_profile($_POST["recipe"]["id"])[0]);
  }
?>