<div id="recipe-ribbon">
  <div id="recipe-ribbon-left" class="radial title recipe-ribbon-part">
    <p>
      <span id="-1" class="recipe-part recipe-name subtitle clickable pinnable">
        New recipe
      </span>
    </p>
    <?php
      foreach ($recipes as $recipe) {
        print("<p><span id='".$recipe["id"].
	  "' class='recipe-part recipe-name clickable pinnable'>".
	  ucfirst($recipe["name"])."</span></p>");
      }
    ?>
  </div>
  <div id="recipe-ribbon-right" class="title recipe-ribbon-part">
    <p>
      <span name="-1" class="recipe-part" prep-time="" prep-time-active=""
      serving-number="" serving-note="" calories=""></span>
    </p>
    <?php
      foreach ($recipes as $recipe) {
	print("<p><span name='".$recipe["id"]."' class='recipe-part ".
	  "recipe-profile clickable pinnable' prep-time='".$recipe["prep_time"].
          "' prep-time-active='");
	if (isset($recipe["prep_time_active"])) {
	  print($recipe["prep_time_active"]);
	}
	print("' serving-number='".$recipe["serving_number"].
          "' serving-note='");
        if (isset($recipe["serving_note"])) {
          print($recipe["serving_note"]);
        }
        print("' calories='".$recipe["calories"]."'>".$recipe["profile"].
          "</span></p>");
      }
    ?>
  </div>
</div>
<div id="recipe-full">
  <p id="recipe-full-title" class="title">
    <span class="editable singleline">Welcome to RecipeSense!</span>
  </p>
  <div id="recipe-prep" class="recipe-full-profile">
    <span id="recipe-prep-title" class="title">To profile a recipe</span>
    <ul>
      <li id="recipe-prep-item">
        <span id="recipe-prep-total" class="editable singleline">
          Hover on the name
        </span>
        <span id="recipe-prep-active-wrapper" class="noface">
          (<span id="recipe-prep-active" class="editable singleline"></span>
          active)
        </span>
      </li>
    </ul>
  </div
 ><div id="recipe-servings" class="recipe-full-profile">
    <span id="recipe-servings-title" class="title">To select a recipe</span>
    <ul>
      <li id="recipe-servings-item">
        <span id="recipe-serving-number" class="editable singleline">
          Click on the name
        </span>
        <span id="recipe-serving-note-wrapper" class="noface">
          (<span id="recipe-serving-note" class="editable singleline"></span>)
        </span>
      </li>
    </ul>
  </div
 ><div id="recipe-calories" class="recipe-full-profile">
    <span id="recipe-calories-title" class="title">To pin/unpin a recipe</span>
    <ul>
      <li id="recipe-calories-item">
        <span id="recipe-calories-number" class="editable singleline">
          Click on the profile
        </span>
        <span id="recipe-calories-label" class="noface"> per serving</span>
      </li>
    </ul>
  </div>
  <div id="recipe-ingredients" class="recipe-detail-part noface">
    <span class="title">Ingredients</span>
    <div id="recipe-ingredients-items" class="editable">
    </div>
  </div>
  <div id="recipe-instructions" class="recipe-detail-part noface">
    <span class="title">Instructions</span>
    <div id="recipe-instructions-items" class="editable">
    </div>
  </div>
  <div id="recipe-ingredients-items-back" class="noface"></div>
  <div id="recipe-instructions-items-back" class="noface"></div>
</div>
<div id="recipe-editmode-wrapper">
  <span id="recipe-edittoggle"
        class="recipe-editmode radial title clickable pinnable">
    EditMode
  </span>
  <span id="recipe-editupdate" 
        class="recipe-editmode radial title clickable noface">
    Update
  </span>
  <span id="recipe-editnotice" class="recipe-editmode"></span>
</div>