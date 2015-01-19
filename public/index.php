<?php
  require("../includes/config.php");

  render("recipe_view.php", ["recipes" => get_recipe_profile()]);
?>