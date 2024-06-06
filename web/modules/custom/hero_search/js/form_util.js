// Update drop-downs with query string

window.onload = function() {
  document.getElementById("search-query-input-all").addEventListener('change', updateSearchers);
  const articles_dd = document.getElementById("search-dropdown-articles");
  const books_dd = document.getElementById("search-dropdown-books");
  const journals_dd = document.getElementById("search-dropdown-journals");
  const articles_base = articles_dd.href;
  const books_base = books_dd.href;
  const journals_base = journals_dd.href;

  function updateSearchers() {
    if (this.value && this.value.trim()) {
      var query_string = "&query=any,contains," + this.value;
      articles_dd.href = articles_base + query_string;
      books_dd.href = books_base + query_string;
      journals_dd.href = journals_base + query_string;
    } else {
      articles_dd.href = articles_base;
      books_dd.href = books_base;
      journals_dd.href = journals_base;
    }
  }
}
