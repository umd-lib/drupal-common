// Fill in form values from arg

window.onload = function() {
  const queryString = window.location.search;
  const urlParams = new URLSearchParams(queryString);
  const bentoSearch = urlParams.get('query');
  if (bentoSearch) {
    document.getElementById("edit-bento-search").value = bentoSearch;
  }
}
