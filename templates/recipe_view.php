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
    <span id="recipe-ingredient-insert" class="title clickable noface">
      &plus;
    </span>
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
  <span id="recipe-edithelpme"
        class="recipe-editmode recipe-editsub clickable">?</span>
  <span id="recipe-edittoggle"
        class="recipe-editmode radial title clickable pinnable">
    EditMode
  </span>
  <span id="recipe-editupdate" 
        class="recipe-editmode recipe-editsub radial title clickable noface">
    Update
  </span>
  <span id="recipe-editdelete"
        class="recipe-editmode recipe-editsub radial title clickable noface">
    Delete
  </span>
  <span id="recipe-editnotice" class="recipe-editmode recipe-editsub"></span>
</div>
<div id="overlay-delete" class="overlay noface"><div class="dialog-wrapper">
  <div class="dialog">
    <span class="dialog-prompt title">Confirm delete?</span>
    <span id="dialog-delete-yes"
      class="dialog-delete dialog-yes clickable">Yes</span
    ><span class="dialog-delete clickable">No</span>
  </div>
</div></div>
<div id="overlay-ingredient-insert" class="overlay noface">
  <div class="dialog-wrapper"><div class="dialog">
    <span class="dialog-prompt title">Insert ingredient?</span>
    <div class="dialog-prompt title">
      <span>Name:</span> <input id="ingredient-insert-name" type="text" />
    </div>
    <div class="dialog-prompt title">
      <span>Plural:</span> <input id="ingredient-insert-plural" type="text" />
    </div>
    <span id="dialog-ingredient-insert-yes"
      class="dialog-ingredient-insert dialog-yes clickable">Yes</span
    ><span class="dialog-ingredient-insert clickable">No</span>
  </div></div>
</div>
<div id="overlay-edithelpme" class="overlay noface">
  <div class="dialog-wrapper"><div class="dialog">
    <span class="dialog-prompt title">About EditMode</span>
    <span class="dialog-prompt subtitle">Update</span>
    <ul>
      <li>Adds a new recipe</li>
      <li>Updates an existing recipe</li>
    </ul>
    <span class="dialog-prompt subtitle">Delete</span>
    <ul><li>Deletes an existing recipe</li></ul>
    <span class="dialog-prompt subtitle">Notifications</span>
    <ul>
      <li>&check;: All OK</li>
      <li>ERROR: It's dead, Jim</li>
      <li>ABORTED POST: Post not attempted</li>
      <li>BAD POST: Post partially or completely failed</li>
    </ul>
    <span class="dialog-edithelpme clickable">OK</span>
  </div></div>
</div>