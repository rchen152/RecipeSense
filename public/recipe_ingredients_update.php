<?php
  require("../includes/config.php");

  if (isset($_POST["recipe"]) && isset($_POST["recipe"]["id"])) {

    /******** UPDATE RECIPE INGREDIENTS ********/

    if (isset($_POST["recipe"]["ingredients"])) {
      $parameters = array();

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
            return;
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
      }
    }

    // Success
    echo json_encode("&check;");
  }
?>