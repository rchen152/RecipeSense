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
      echo json_encode("recipe insert failure");
      return;
    }
    $query_str = "";
    $parameters = array();

    // Return insert id
    echo json_encode(query("SELECT LAST_INSERT_ID()")[0]["LAST_INSERT_ID()"]);
  }
?>