// Toggle the search button (in tablet and smartphone view) and utility buttons (in desktop and tablet view.)

function setDisplay(elem_id, val) {
  let elem = document.getElementById(elem_id)
  if (elem) {
    elem.style.display = val;
  }
}
function getDisplay(elem_id) {
  let elem = document.getElementById(elem_id)
  if (elem) {
    return elem.style.display;
  }
}
function setBackground(elem_id, val) {
  let elem = document.getElementById(elem_id)
  if (elem) {
    elem.style.background = val;
  }
}
function setFocus(elem_id) {
  let elem = document.getElementById(elem_id)
  if (elem) {
    elem.focus();
  }
}

function toggleBlockAndButton(block, button) {
  if (getDisplay(block) === "block") {
    setDisplay(block, "none");
    setBackground(button, "#fff");
  } else {
    setDisplay(block, "block");
    setFocus(block);
    setBackground(button, "#eee");
  }
  var blocks = ["showHours", "showSystem", "showHelp", "showInfo", "showAccounts", "showSearch"];
  var buttons = ["u-hours-btn", "u-system-btn", "u-help-btn", "u-info-btn", "u-accounts-btn", "u-search-btn"]
  blocks.filter(b => b != block).forEach(block => setDisplay(block, "none"));
  buttons.filter(b => b != button).forEach(button => setBackground(button, "#fff"));
}

function exitBlockAndButton(block, button) {
  setDisplay(block, "none");
  setBackground(button, "#fff");
}

// Toggle Search //
function searchToggle() {
  toggleBlockAndButton("showSearch", "u-search-btn");
}

// Toggle Hours //
function hoursToggle() {
  toggleBlockAndButton("showHours", "u-hours-btn");
}

// Toggle System //
function systemToggle() {
  toggleBlockAndButton("showSystem", "u-system-btn");
}

// Toggle Help //
function helpToggle() {
  toggleBlockAndButton("showHelp", "u-help-btn");
}
// Toggle Info //
function infoToggle() {
  toggleBlockAndButton("showInfo", "u-info-btn");
}
// Toggle Accounts //
function accountsToggle() {
  toggleBlockAndButton("showAccounts", "u-accounts-btn");
}

function exitHours() {
  exitBlockAndButton("showHours", "u-hours-btn");
}
function exitSystem() {
  exitBlockAndButton("showSystem", "u-system-btn");
}
function exitInfo() {
  exitBlockAndButton("showInfo", "u-info-btn");
}
function exitAccounts() {
  exitBlockAndButton("showAccounts", "u-accounts-btn");
}
function exitHelp() {
  exitBlockAndButton("showHelp", "u-help-btn");
}
function exitSearch() {
  exitBlockAndButton("showSearch", "u-search-btn");
}
