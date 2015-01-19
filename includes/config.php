<?php
  ini_set("display_errors", true);
  error_reporting(E_ALL);

  require("constants.php");
  require("functions.php");

  session_start();

  set_unit_ids("ingredient_units",
    "SELECT unit.id, unit.name, unit.abbreviation FROM ".
    "unit INNER JOIN unit_type ON unit.unit_type_id = unit_type.id ".
    "WHERE unit_type.name = 'volume' OR unit_type.name = 'weight'");
  set_unit_ids("time_units",
    "SELECT unit.id, unit.name, unit.abbreviation FROM ".
    "unit INNER JOIN unit_type ON unit.unit_type_id = unit_type.id ".
    "WHERE unit_type.name = 'time'");
  $_SESSION["ingredients"] = array();
  set_recipe_ingredient_group_ids();
?>