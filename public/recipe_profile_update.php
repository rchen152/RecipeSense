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
        echo json_encode(get_recipe_profile_with_error($_POST["recipe"]["id"],
          "unrecognized time unit: ".$prep[1]));
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
          echo json_encode(get_recipe_profile_with_error($_POST["recipe"]["id"],
            "unrecognized active time unit: ".$prep_active[1]));
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
        echo json_encode(get_recipe_profile_with_error($_POST["recipe"]["id"],
          "profile update failure"));
        return;
      }
      $query_str = "";
      $parameters = array();
    }

    // Return updated profile
    echo json_encode(get_recipe_profile_with_error($_POST["recipe"]["id"]));
  }
?>