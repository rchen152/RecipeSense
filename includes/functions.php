<?php
  require_once("constants.php");

  /**
   * Escapes special characters to safely display in HTML
  **/
  function escape_html($str) {
    if (!isset($_SESSION["html_chars"])) {
      $_SESSION["html_chars"] = array(
        "&" => "&amp;",
        "<" => "&lt;",
        ">" => "&gt;",
        "'" => "&apos;",
        '"' => "&quot;"
      );
    }
    foreach (array_keys($_SESSION["html_chars"]) as $html_char) {
      $str = str_replace($html_char, $_SESSION["html_chars"][$html_char], $str);
    }
    return $str;
  }

  /**
   * Executes SQL statement, possibly with parameters, returning an array of all
   * rows in result set or false on (non-fatal) error.
  **/
  function query($sql, $parameters = []) {
    static $handle;
    if (!isset($handle)) {
      try {
        $handle = new PDO("mysql:dbname=" . DATABASE . ";host=" . SERVER,
	                  USERNAME, PASSWORD);
        $handle->setAttribute(PDO::ATTR_EMULATE_PREPARES, false); 
      } catch (Exception $e) {
        trigger_error($e->getMessage(), E_USER_ERROR);
        exit;
      }
    }
 
    $statement = $handle->prepare($sql);
    if ($statement === false) {
      trigger_error($handle->errorInfo()[2], E_USER_ERROR);
      exit;
    }
 
    $results = $statement->execute($parameters);
    if ($results !== false) {
      return $statement->fetchAll(PDO::FETCH_ASSOC);
    } else {
      return false;
    }
  }

  /**
   * Populates the unit array using the given SQL query
  **/
  function set_unit_ids($unit_arr, $query) {
    $result = query($query);
    $_SESSION[$unit_arr] = array();
    foreach ($result as $unit) {
      $_SESSION[$unit_arr][$unit["name"]] = $unit["id"];
      $_SESSION[$unit_arr][$unit["abbreviation"]] = $unit["id"];
    }
  }

  /**
   * Gets the id for the given unit, -1 if not found.
  **/
  function get_unit_id($unit, $arr_prefix) {
    $arr_name = $arr_prefix."_units";
    if (!isset($_SESSION[$arr_name]) ||
        !isset($_SESSION[$arr_name][$unit])) {
      return -1;
    }
    return $_SESSION[$arr_name][$unit];
  }

  /**
   * Gets the id for the given ingredient, -1 if not found.
  **/
  function get_ingredient_id($name) {
    if (!isset($_SESSION["ingredients"][$name])) {
      $result = query("SELECT id FROM ingredient WHERE name = ?", [$name]);
      if (!count($result)) {
        return -1;
      }
      $_SESSION["ingredients"][$name] = $result[0]["id"];
    }
    return $_SESSION["ingredients"][$name];
  }

  /**
   * Populates an array of recipe ingredient groups.
  **/
  function set_recipe_ingredient_group_ids() {
    $result = query("SELECT id, name FROM recipe_ingredient_group");
    $_SESSION["ingredient_groups"] = array();
    foreach ($result as $group) {
      $_SESSION["ingredient_groups"][$group["name"]] = $group["id"];
    }
  }

  /**
   * Gets the id for the recipe ingredient group, -1 if not found.
  **/
  function get_recipe_ingredient_group_id($group_name) {
    if (!isset($_SESSION["ingredient_groups"][$group_name])) {
      query("INSERT INTO recipe_ingredient_group(name) VALUES(?)",
        [$group_name]);
      $id = query("SELECT id FROM recipe_ingredient_group WHERE name = ?",
        [$group_name]);
      if (!count($id)) {
        return -1;
      }
      $_SESSION["ingredient_groups"][$group_name] = $id[0]["id"];
    }
    return $_SESSION["ingredient_groups"][$group_name];
  }

  /**
   * Gets the the recipe profile for the given id or all profiles for id < 1.
  **/
  function get_recipe_profile($id = -1) {
    $query_str =
      "SELECT recipe.id, recipe.name, recipe.prep_time, ".
             "IFNULL(unit.abbreviation, unit.name) AS unit, ".
  	     "recipe.prep_time_active, ".
	     "IFNULL(unit_active.abbreviation, unit_active.name) AS ".
	       "unit_active, ".
             "recipe.serving_number, recipe.serving_note, recipe.calories ".
      "FROM recipe LEFT OUTER JOIN unit ON recipe.prep_time_unit_id = unit.id ".
                  "LEFT OUTER JOIN unit AS unit_active ON ".
                  "recipe.prep_time_active_unit_id = unit_active.id";
    if ($id > 0) {
      $recipes_query = query($query_str." WHERE recipe.id = ?", [$id]);
    } else {
      $recipes_query = query($query_str);
    }

    $recipes = array();
    foreach ($recipes_query as $recipe_query) {
      $recipe = array();

      $recipe["id"] = escape_html($recipe_query["id"]);
      $recipe["name"] = escape_html($recipe_query["name"]);

      $recipe["profile"] = "";

      $recipe["prep_time"] = escape_html($recipe_query["prep_time"]." ".
  	$recipe_query["unit"]);
      $recipe["profile"] .= "&compfn;&ensp;".escape_html($recipe["prep_time"]);
      if (isset($recipe_query["prep_time_active"])) {
        $recipe["prep_time_active"] = escape_html(
          $recipe_query["prep_time_active"]." ".$recipe_query["unit_active"]);
      }

      $recipe["profile"] .= "&emsp;&compfn;&ensp;";
      $recipe["serving_number"] = escape_html($recipe_query["serving_number"]);
      $calories = round($recipe_query["calories"], 2);
      $recipe["calories"] = escape_html($calories);
      if ($recipe_query["serving_number"] != 1) {
        $recipe["profile"] .= escape_html($recipe_query["serving_number"]).
          " &times; ";
      }
      $recipe["profile"] .= $recipe["calories"]." cal";

      if (isset($recipe_query["serving_note"])) {
        $recipe["serving_note"] = escape_html($recipe_query["serving_note"]);
      }

      $recipes[] = $recipe;
    }

    return $recipes;
  }

  /**
   * Renders template, passing in values
  **/
  function render($template, $values = []) {
    if (file_exists("../templates/$template")) {
      extract($values);
      require("../templates/header.php");
      require("../templates/$template");
      require("../templates/footer.php");
    } else {
      trigger_error("Invalid template: $template", E_USER_ERROR);
    }
  }
?>