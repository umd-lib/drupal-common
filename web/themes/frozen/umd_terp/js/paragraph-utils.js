// Get the hash value from the window on load
var hash = window.location.hash.substr(1);
CheckHashID(hash);

// Attach a listener for additional anchor events
if (document.addEventListener) {
  document.addEventListener('click', ParseHash, false);
} else {
  document.attachEvent('click', ParseHash);
}

// Evaluate click actions
function ParseHash(e) {
  var e = window.e || e;

  if (e.target.tagName !== 'A') {
    return;
  }

  // Check if clicked URL has hash
  var link = e.target.getAttribute("href");
  if (link != "") {
    var hash = link.split("#")[1];
    CheckHashID(hash);
  }
}

// Evaluate if has has corresponding ID
function CheckHashID(hash) {
  if (hash != "") {
    var idElement = document.getElementById(hash);
    if (idElement != null) {
      // Check of collapsable
      if (idElement.classList.contains("collapse") && !idElement.classList.contains("show")) {
        // if collapsable, set to not collapsed
        idElement.classList.add("show");
      }
    }
  }
}

