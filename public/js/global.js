$(document).ready(function() {
  $(".logo").click(function() {
    $(location).attr("href", "");
  });

  initNameAndProfile(".recipe-name", ".recipe-profile");

  $("#recipe-edittoggle").click(toggleEditMode);
  $("#recipe-editupdate").click(function() {
    var validated = validateEdits();
    if (typeof validated === typeof "") {
      notify(validated);
      return;
    }
    if ($("#recipe-full-title").attr("name") < 0) {
      insertRecipe(validated);
    } else {
      updateRecipe(validated);
    }
  });
  $("#recipe-editdelete").click(function() {
    if ($("#recipe-full-title").attr("name") < 0) {
      notify("nop");
    } else {
      $("#overlay").show();
    }
  });
  $("#dialog-yes").click(deleteRecipe);
  $(".dialog-option").click(function() { $("#overlay").hide(); });

  $(".editable").attr("onpaste", "sanitizePaste(event)");
  $(".singleline").keypress(function(event) { return event.which !== 13; });
  $(".noface").hide();
});

var initNameAndProfile = function(name, profile) {
  $(name).mouseenter(function() { showProfile($(this).attr("id")); })
                   .mouseleave(function() {
                     if (!$(this).hasClass("selected") &&
                         !$(this).hasClass("pinned")) {
                       hideProfile($(this).attr("id"));
                     }
                   })
                   .click(function() { select($(this).attr("id")); });
  $(profile).click(function() { togglePin($(this).attr("name")); });
};

var normalize = function(text, keepCase) {
  if (!keepCase) {
    text = text.toLowerCase();
  }
  return text.trim().replace(/\s+/g, " ");
}

/******** Recipe ribbon ********/

var showProfile = function(recipeId) {
  $("#recipe-ribbon-right").find("[name='" + recipeId + "']")
    .css("visibility", "visible");
};

var hideProfile = function(recipeId) {
  $("#recipe-ribbon-right").find("[name='" + recipeId + "']")
    .removeAttr("style");
};

var select = function(recipeId) {
  if (!$("#" + recipeId).hasClass("selected")) {
    var recipeNames = $(".recipe-name");
    for (var i = 0; i < recipeNames.length; ++i) {
      var id = $(recipeNames[i]).attr("id");
      if (id !== recipeId) {
      unselect(id);
      }
    }
    $("#" + recipeId).addClass("selected").css("background", "#ffccff")
                     .css("font-weight", "bold");
  }
  showRecipeDetail(recipeId, recipeId < 0);
};

var unselect = function(recipeId) {
  $("#" + recipeId).removeClass("selected");
  if (!$("#" + recipeId).hasClass("pinned")) {
    $("#" + recipeId).removeAttr("style");
    hideProfile(recipeId);
  }
};

var togglePin = function(recipeId) {
  if (!$("#" + recipeId).hasClass("pinned")) {
    $("#" + recipeId).addClass("pinned");
    $("#recipe-ribbon-right").find("[name='" + recipeId + "']")
      .css("background", "#660000").css("font-weight", "bold");
  } else {
    $("#" + recipeId).removeClass("pinned");
    $("#recipe-ribbon-right").find("[name='" + recipeId + "']")
      .css("background", "").css("font-weight", "");
    if (!$("#" + recipeId).hasClass("selected")) {
      unselect(recipeId);
    }
  }
}

/******** RECIPE FULL ********/

var showRecipeDetail = function(recipeId, forceUpdate) {
  if (!forceUpdate && $("#recipe-full-title").attr("name") === recipeId) {
    return;
  }

  if (recipeId >= 0) {
    $.ajax({
      type: "POST",
      url: "/recipe_detail.php",
      data: { recipe_id: recipeId },
      dataType: "json"
    }).done(function(recipeData) {
      loadRecipeDetail(recipeId, recipeData);
    });
  } else {
    loadRecipeDetail(recipeId);
  }
};

var loadRecipeDetail = function(recipeId, recipeData) {
  initRecipeDetail(recipeId);
  $(".recipe-detail-part").show();
  $("#recipe-ingredients-items, #recipe-instructions-items").empty();

  if (recipeId < 0) {
    if (!$("#recipe-edittoggle").hasClass("on")) {
      toggleEditMode();
    }
    $("#recipe-ingredients-items-back, #recipe-instructions-items-back")
      .empty();
    return;
  }

  var groups = Object.keys(recipeData.ingredients);
  if (recipeData.ingredients.hasOwnProperty("None")) {
    $("#recipe-ingredients-items").append("<ul></ul>");
    for (var i = 0; i < recipeData.ingredients.None.length; ++i) {
      $("#recipe-ingredients-items ul").append("<li></li>");
      $("#recipe-ingredients-items ul li:last").text(
        recipeData.ingredients.None[i]);
    }
  } else if (groups.length > 0) {
    $("#recipe-ingredients-items").append("<br />");
  }
  for (var i = 0; i < groups.length; ++i) {
    if (groups[i] !== "None") {
      $("#recipe-ingredients-items").append("<span class='subtitle'></span>");
      $("#recipe-ingredients-items span:last").text(groups[i]);
      $("#recipe-ingredients-items").append("<ul></ul>");
      for (var j = 0; j < recipeData.ingredients[groups[i]].length; ++j) {
        $("#recipe-ingredients-items ul:last").append("<li></li>");
        $("#recipe-ingredients-items ul:last li:last").text(
          recipeData.ingredients[groups[i]][j]);
      }
    }
  }

  var instructionCounter = 1;
  var inList = false;
  for (var i = 0; i < recipeData.instructions.length; ++i) {
    if (recipeData.instructions[i].show_number === 0) {
      if (!inList) {
        $("#recipe-instructions-items").append("<br />");
      }
      inList = false;;
      $("#recipe-instructions-items").append(
        "<span class='subtitle'></span>");
      $("#recipe-instructions-items span:last").text(
        recipeData.instructions[i].description);
    } else {
      if (!inList) {
        currList = $("#recipe-instructions-items").append(
          "<ol start='" + instructionCounter + "'></ol>");
      }
      inList = true;
      $("#recipe-instructions-items ol:last").append("<li></li>");
      $("#recipe-instructions-items ol:last li:last").text(
        recipeData.instructions[i].description);
      ++instructionCounter;
    }
  }

  $("#recipe-ingredients-items-back").html(
    $("#recipe-ingredients-items").html());
  $("#recipe-instructions-items-back").html(
    $("#recipe-instructions-items").html());
};

var initRecipeDetail = function(recipeId) {
  var profile = $("#recipe-ribbon-right").find("[name='" + recipeId + "']");

  $("#recipe-full-title span").html($("#" + recipeId).html());
  $("#recipe-full-title").attr("name", recipeId);

  $("#recipe-prep-title").html("Time");
  if (profile.attr("prep-time")) {
    $("#recipe-prep-total").html(profile.attr("prep-time"));
  } else {
    $("#recipe-prep-total").html("&nbsp;");
  }
  if (profile.attr("prep-time-active")) {
    $("#recipe-prep-active").html(profile.attr("prep-time-active"));
    $("#recipe-prep-active-wrapper").show();
  } else {
    $("#recipe-prep-active").html("&nbsp;");
    $("#recipe-prep-active-wrapper").hide();
  }

  $("#recipe-servings-title").html("Servings");
  if (profile.attr("serving-number")) {
    $("#recipe-serving-number").html(profile.attr("serving-number"));
  } else {
    $("#recipe-serving-number").html("&nbsp;");
  }
  if (profile.attr("serving-note")) {
    $("#recipe-serving-note").html(profile.attr("serving-note"));
    $("#recipe-serving-note-wrapper").show();
  } else {
    $("#recipe-serving-note").html("&nbsp;");
    $("#recipe-serving-note-wrapper").hide();
  }

  $("#recipe-calories-title").html("Calories");
  if (profile.attr("calories")) {
    $("#recipe-calories-number").html(profile.attr("calories"));
  } else {
    $("#recipe-calories-number").html("&nbsp;");
  }
  if (profile.attr("serving-number") != 1) {
    $("#recipe-calories-label").show();
  } else {
    $("#recipe-calories-label").hide();
  }

  if ($("#recipe-edittoggle").hasClass("on")) {
    editModeOn();
  }
};

/******** EDIT MODE ********/

var toggleEditMode = function() {
  if (!$("#recipe-full-title").is("[name]")) {
    return;
  }
  if (!$("#recipe-edittoggle").hasClass("on")) {
    $("#recipe-edittoggle").addClass("on").css("background", "#ffccff")
                           .css("font-weight", "bold");
    $("#recipe-editnotice").text("");
    $("#recipe-editupdate, #recipe-editdelete, #recipe-editnotice").show();
    editModeOn();
  } else {
    $("#recipe-edittoggle").removeClass("on").removeAttr("style");
    $("#recipe-editupdate, #recipe-editdelete, #recipe-editnotice").hide();
    editModeOff();
  }
};

var editModeOn = function() {
  $(".editable").css("background", "rgba(153, 0, 0, 0.4)")
                .attr("contenteditable", "true");
  $("#recipe-prep-active-wrapper, #recipe-serving-note-wrapper").show();
};

var editModeOff = function() {
  $(".editable").removeAttr("style").removeAttr("contenteditable");
  initRecipeDetail($("#recipe-full-title").attr("name"));
  $("#recipe-ingredients-items").html(
    $("#recipe-ingredients-items-back").html());
  $("#recipe-instructions-items").html(
    $("#recipe-instructions-items-back").html());
};

var notify = function(msg) {
  $("#recipe-editnotice").hide().html(msg).show(200);
}

var sanitizePaste = function(event) {
  if (event.preventDefault) {
    event.preventDefault();
  }
  if (event.stopPropagation) {
    event.stopPropagation();
  }

  var text = "";
  if (event.clipboardData && event.clipboardData.getData) {
    text = event.clipboardData.getData("text/plain");
  } else if (window.clipboardData && window.clipboardData.getData) {
    text = window.clipboardData.getData("Text");
  } else {
    notify("clipboard unavailable");
    return;
  }

  if (window.getSelection) {
    var selection = window.getSelection();
    if (selection.rangeCount) {
      var range = selection.getRangeAt(0);
      range.deleteContents();
      var textNode = document.createTextNode(text);
      range.insertNode(textNode);
      range.setStartAfter(textNode);
      selection.removeAllRanges();
      selection.addRange(range);
      return;
    }
  }
  notify("selection unavailable");
};

/******** RECIPE PRE-PARSE ********/

var validateEdits = function() {
  var id = $("#recipe-full-title").attr("name");
  var profile = $("#recipe-ribbon-right").find("[name='" + id + "']");
  var recipe = { id: id };
  var update = false;

  var name = normalize($("#recipe-full-title span").text());
  if (!name) {
    return "empty name";
  }
  if (name !== normalize($("#" + id).text())) {
    update = true;
    recipe.name = name;
  }

  var prepTime = normalize($("#recipe-prep-total").text());
  var prep = prepTime.split(" ");
  if (!prepTime || !timeExp(prep)) {
    return "bad time: " + prepTime;
  }
  if (timeDiff(prep, profile.attr("prep-time").split(" "))) {
    update = true;
    recipe.prep_time = prepTime;
  }

  var prepTimeActive = normalize($("#recipe-prep-active").text());
  var prepActive = prepTimeActive.split(" ");
  if (prepTimeActive && !timeExp(prepActive)) {
    return "bad active time: " + prepTimeActive;
  }
  if (timeDiff(prepActive, profile.attr("prep-time-active").split(" "))) {
    update = true;
    recipe.prep_time_active = prepTimeActive;
  }
  
  var servingNumber = normalize($("#recipe-serving-number").text());
  if (!numExp(servingNumber)) {
    return "bad serving number: " + servingNumber;
  }
  if (+servingNumber != +profile.attr("serving-number")) {
    update = true;
    recipe.serving_number = servingNumber;
  }

  var servingNote = normalize($("#recipe-serving-note").text());
  if (servingNote !== profile.attr("serving-note")) {
    update = true;
    recipe.serving_note = servingNote;
  }

  var calories = normalize($("#recipe-calories-number").text());
  if (!numExp(calories)) {
    return "bad calorie count: " + calories;
  }
  if (+calories != +profile.attr("calories")) {
    update = true;
    recipe.calories = calories;
  }

  if ($("#recipe-ingredients-items").html() !==
      $("#recipe-ingredients-items-back").html()) {
    update = true;
    var ingredients = parseIngredients();
    if (typeof ingredients === typeof "") {
      return ingredients;
    }
    recipe.ingredients = ingredients.length ? ingredients : false;
  }

  if ($("#recipe-instructions-items").html() !==
      $("#recipe-instructions-items-back").html()) {
    update = true;
    var instructions = parseInstructions();
    recipe.instructions = instructions.length ? instructions : false;
  }

  return update ? recipe : "nop";
};

var timeExp = function(time) {
  return time.length === 2 && !isNaN(time[0]) && +time[0] > 0;
};

var timeDiff = function(time1, time2) {
  return time1.length !== time2.length ||
    (time1.length === 2 && (+time1[0] != +time2[0] || time1[1] !== time2[1]));
};

var numExp = function(num) {
  return !isNaN(num) && +num > 0;
};

/******** RECIPE INSERT ********/

var insertRecipe = function(validated) {
  $.ajax({
    type: "POST",
    url: "/recipe_insert.php",
    data: { recipe: validated },
    dataType: "json"
  }).done(function(updated) {
    if (typeof updated === typeof "") {
      notify(updated);
      return;
    }
    $("#recipe-ribbon-left").append("<p><span id='" + updated.id +
      "' class='recipe-part recipe-name clickable pinnable'></span></p>");
    $("#recipe-ribbon-right").append("<p><span name='" + updated.id +
      "' class='recipe-part recipe-profile clickable'></span</p>");
    initNameAndProfile("#" + updated.id, "#recipe-ribbon-right span:last");
    loadUpdatedData(updated);
    showProfile(updated.id);
    select(updated.id);
    notify("&check;");
  });
};

/******** RECIPE DELETE ********/
var deleteRecipe = function() {
  $.ajax({
    type: "POST",
    url: "/recipe_delete.php",
    data: { recipe_id: $("#recipe-full-title").attr("name") },
    dataType: "json"
  }).done(function(deleteId) {
    if (isNaN(deleteId)) {
      notify(deleteId);
      return;
    }
    $("#" + deleteId).remove();
    $("#recipe-ribbon-right").find("[name='" + deleteId + "']").remove();
    select(-1);
    notify("&check;");
  });
};

/******** RECIPE UPDATE ********/

var updateRecipe = function(validated) {
  $.ajax({
    type: "POST",
    url: "/recipe_update.php",
    data: { recipe: validated },
    dataType: "json"
  }).done(function(updated) {
    if (typeof updated === typeof "") {
      notify(updated);
      return;
    }
    loadUpdatedData(updated);
    notify("&check;");
  });
};

var loadUpdatedData = function(updated) {
  var profile = $("#recipe-ribbon-right").find("[name='" + updated.id + "']");
  $("#" + updated.id).html(
    updated.name.charAt(0).toUpperCase() + updated.name.substring(1));

  profile.attr("prep-time", updated.prep_time)
         .attr("serving-number", updated.serving_number)
         .attr("calories", updated.calories)
         .html(updated.profile);
  if (updated.hasOwnProperty("prep_time_active")) {
    profile.attr("prep-time-active", updated.prep_time_active);
  } else {
    profile.attr("prep-time-active", "");
  }
  if (updated.hasOwnProperty("serving_note")) {
    profile.attr("serving-note", updated.serving_note);
  } else {
    profile.attr("serving-note", "");
  }

  showRecipeDetail(updated.id, true);
};

/******** UPDATE INGREDIENTS ********/

var parseIngredients = function() {
  var nodes = Array.prototype.slice.call(
    $("#recipe-ingredients-items").prop("childNodes"));
  var ingredients = [];
  var groupName = "none";
  while (nodes.length > 0) {
    var node = nodes.shift();
    if (/ul/i.test(node.nodeName)) {
      var group = parseIngredientGroup(node, groupName);
      if (typeof group === typeof "") {
        return group;
      }
      ingredients = ingredients.concat(group);
    } else if (node.nodeType === Node.TEXT_NODE) {
      var text = normalize(node.nodeValue);
      if (text) {
        var start = text.charAt(0);
        if (start === '>' || start === '-') {
          text = normalize(text.substring(1));
          if (text) {
            ingredient = parseIngredient(text);
            if (typeof ingredient === typeof "") {
              return ingredient;
            }
            ingredient.group_name = groupName;
            ingredients[ingredients.length] = ingredient;
          }
        } else {
          groupName = text;
        }
      }
    } else {
      nodes = Array.prototype.slice.call(node.childNodes).concat(nodes);
    }
  }
  return ingredients;
};

var parseIngredientGroup = function(ul, groupName) {
  var group = [];
  var nodes = $(ul).children();
  for (var i = 0; i < nodes.length; ++i) {
    var text = normalize($(nodes[i]).text());
    if (text) {
      ingredient = parseIngredient(text);
      if (typeof ingredient === typeof "") {
        return ingredient;
      }
      ingredient.group_name = groupName;
      group[group.length] = ingredient;
    }
  }
  return group;
};

var parseIngredient = function(ingredient) {
  var ingredientArr = ingredient.split(" ");
  switch (ingredientArr.length) {
    case 0: case 1: {
      return "bad ingredient: " + ingredient;
    } default: {
      if (!numExp(ingredientArr[0])) {
        return "bad ingredient quantity: " + ingredientArr[0];
      } else {
        return {
          quantity: ingredientArr[0],
          description: ingredientArr.slice(1).join(" ")
        };
      }
    }
  }
};

/******** UPDATE INSTRUCTIONS ********/

var parseInstructions = function() {
  var nodes = Array.prototype.slice.call(
    $("#recipe-instructions-items").prop("childNodes"));
  var instructions = [];
  var instructionNumber = 1;
  while (nodes.length > 0) {
    var node = nodes.shift();
    if (/ol/i.test(node.nodeName)) {
      var group = parseInstructionGroup(node, instructionNumber);
      instructions = instructions.concat(group);
      instructionNumber += group.length;
    } else if (node.nodeType === Node.TEXT_NODE) {
      var text = normalize(node.nodeValue, true);
      var step = /^\d+\./.test(text);
      if (step) {
        text = normalize(text.substring(text.indexOf(".") + 1), true);
      }
      if (text) {
        if (step || !instructions.length ||
            instructions[instructions.length - 1].show_number) {
          instructions[instructions.length] = {
            number: instructionNumber++,
            show_number: step ? 1 : 0,
            description: text
          };
        } else {
          instructions[instructions.length - 1].description += " " + text;
        }
      }
    } else {
      nodes = Array.prototype.slice.call(node.childNodes).concat(nodes);
    }
  }
  return instructions;
};

var parseInstructionGroup = function(ol, startNumber) {
  var group = [];
  var nodes = $(ol).children();
  for (var i = 0; i < nodes.length; ++i) {
    var text = normalize($(nodes[i]).text(), true);
    if (text) {
      group[group.length] = {
        number: startNumber++,
        show_number: 1,
        description: text
      };
    }
  }
  return group;
};
