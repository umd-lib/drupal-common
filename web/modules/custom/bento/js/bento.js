// bento.js

// Select the node that will be observed for mutations
var targetNode = document.getElementById("terp-page-content");

// Options for the observer (which mutations to observe)
var config = { attributes: false, childList: true, subtree: true };

var searchers = [
  "bento-lib-answers",
  "bento-lib-guides",
  "bento-world-cat-discovery-api",
  "bento-world-cat-discovery-api-article",
  "bento-libraries-website",
  "bento-database-finder"
  ];

var searcherTotalsMap = new Map();

// Callback function to execute when mutations are observed
var callback = function(mutationsList, observer) {
  for(var mutation of mutationsList) {
    updateSearcherTotals();
  }
};

// Create an observer instance linked to the callback function
var observer = new MutationObserver(callback);

// Start observing the target node for configured mutations
observer.observe(targetNode, config);

// Updates the result totals for the searcher in the "Found" bar
function updateSearcherTotals() {
  for(var searcher of searchers) {
    s = document.getElementById(searcher+"-total");
    if (s) {
      var elementIndex = searchers.indexOf(searcher);
      searchers.splice(elementIndex, 1);
      if (!searcherTotalsMap.has(s)) {
        s.dataset.total;
        searcherTotalsMap.set(searcher, s.dataset.total);
        searcherTotal = document.getElementById(searcher+"-summary");
  
        if (s.dataset.total == 0) {
          searcherTotal.parentNode.classList.add("bento-no-results");
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

updateSearcherTotals();
