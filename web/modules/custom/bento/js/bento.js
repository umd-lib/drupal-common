// bento.js

// Select the node that will be observed for mutations
var targetNode = document.getElementById("searchers");

// Options for the observer (which mutations to observe)
var config = { attributes: false, childList: true, subtree: true };

var searchers = [
  "bento-archives-space",
  "bento-DRUM",
  "bento-ebsco-discovery-service-api",
  "bento-ebsco-discovery-service-api-article",
  "bento-database-finder",
  "bento-fedora-collections",
  "bento-fedora2-collections",
  "bento-internet-archive",
  "bento-lib-answers",
  "bento-lib-guides",
  "bento-maryland-map",
  "bento-world-cat-discovery-api",
  "bento-world-cat-discovery-api-article",
  "bento-libraries-website",
  "bento-lib-guides-database"
  ];

var searcherTotalsMap = new Map();

// Callback function to execute when mutations are observed
var callback = function(mutationsList, observer) {
  for(var mutation of mutationsList) {
    clearSearcherSpinner();
    updateSearcherTotals();
  }
};

// Create an observer instance linked to the callback function
var observer = new MutationObserver(callback);

// Start observing the target node for configured mutations
observer.observe(targetNode, config);

// Clears the spinners for each searcher, once the actual
// result is available
function clearSearcherSpinner() {
  for(var searcher of searchers) {
    s = document.getElementById(searcher);
    if (s) {
      let spinnerDiv = document.getElementById(searcher + "-spinner");
      if (spinnerDiv) {
        spinnerDiv.parentNode.removeChild(spinnerDiv);
      }
    }
  }
}

// Updates the result totals for the searcher in the "Found" bar
function updateSearcherTotals() {
  for(var searcher of searchers) {
    s = document.getElementById(searcher);
    if (s) {
      if (!searcherTotalsMap.has(s)) {
        s.dataset.total;
        searcherTotalsMap.set(searcher, s.dataset.total);
        searcherTotal = document.getElementById(searcher+"-total");
  
        loadedLinkElement = document.getElementById(searcher+"-loaded-link");
        if (loadedLinkElement) {
          loadedLinkUrl = loadedLinkElement.getAttribute("href");
          if (loadedLinkUrl) {
            endpointTitleElement = document.getElementById(searcher+"-title");
            endpointTitleElement.setAttribute("href", loadedLinkUrl);
          }
        }
        
        if (s.dataset.total == 0) {
          searcherTotal.parentNode.classList.add("font-black");
          searcherTotal.innerHTML = "(" + s.dataset.total + ") ";
        }
        if (s.dataset.total > 0) {
          searcherTotal.parentNode.classList.remove("btn-default");
        }
        if (s.dataset.total <= 100) {
          searcherTotal.innerHTML = "(" + s.dataset.total + ") ";
        } 
        if (s.dataset.total > 100) {
          searcherTotal.innerHTML = "(100+) ";
        }
      }
    }
  }
}

clearSearcherSpinner();
updateSearcherTotals();
