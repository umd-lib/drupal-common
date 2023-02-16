// Get the hash value from the window on load
var hash = window.location.hash.substr(1);
CheckHashAccordionID(hash);
CheckHashTabID(hash);

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
    CheckHashAccordionID(hash);
    CheckHashTabID(hash);
  }
}

// Evaluate if hash has corresponding ID and is accordion
function CheckHashAccordionID(hash) {
  if (hash != "" && hash.includes("accordion-")) {
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

// Evaluate if hash has corresponding ID and is tab
function CheckHashTabID(hash) {
  if (hash != "" && hash.includes("tabs-")) {
    var idElement = document.getElementById(hash);
    if (idElement != null) {
      // var hashRoot = hash.substring(0, hash.length - 1);
      if (hash.includes("-tab-")) {
        // The click paradigm makes more sense for tabs
        idElement.click();
      } else if (hash.includes("-pane-")) {
        var tabElement = document.getElementById(hash.replace("-pane-", "-tab-"));
        if (tabElement != null) {
          tabElement.click();
          tabElement.scrollIntoView();
        }
      }
    }
  }
}
