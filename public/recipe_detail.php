<?php
  require("../includes/config.php");

  if (isset($_POST["recipe_id"])) {

    /******** INGREDIENTS ********/

    $ingredients = query(
      "SELECT recipe_ingredient.quantity, ".
             "IFNULL(unit.abbreviation, unit.name) AS unit, ".
	     "ingredient.name AS name, ".
	     "recipe_ingredient_group.name AS ingredient_group ".
      "FROM recipe_ingredient ".
      "LEFT OUTER JOIN unit ON ".
        "recipe_ingredient.unit_id = unit.id ".
      "LEFT OUTER JOIN ingredient ON ".
        "recipe_ingredient.ingredient_id = ingredient.id ".
      "LEFT OUTER JOIN recipe_ingredient_group ON ".
        "recipe_ingredient.recipe_ingredient_group_id = ".
	"recipe_ingredient_group.id ".
      "WHERE recipe_ingredient.recipe_id = ?", [$_POST["recipe_id"]]);

    $ingredient_strs = array();
    foreach ($ingredients as $ingredient) {

      // Name
      $ingredient_str = $ingredient["name"];

      // Unit
      if (isset($ingredient["unit"])) {
	$ingredient_str = $ingredient["unit"]." ".$ingredient_str;
      }

      // Quantity
      $ingredient_str = $ingredient["quantity"]." ".$ingredient_str;

      // Group
      $group = ucfirst($ingredient["ingredient_group"]);
      if (!isset($ingredient_strs[$group])) {
        $ingredient_strs[$group] = array();
      }
      $ingredient_strs[$group][] = $ingredient_str;
    }

    /******** INSTRUCTIONS ********/

    $instructions = query("SELECT number, show_number, description FROM ".
      "recipe_instruction WHERE recipe_id = ?", [$_POST["recipe_id"]]);

    $recipe_detail = array();
    $recipe_detail["ingredients"] = $ingredient_strs;
    $recipe_detail["instructions"] = $instructions;

    echo json_encode($recipe_detail);
  }
?>