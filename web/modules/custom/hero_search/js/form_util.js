// Update drop-downs with query string

window.onload = function() {
  const searchInput = document.getElementById("search-query-input-all");
  const articlesDd = document.getElementById("search-dropdown-articles");
  const booksDd = document.getElementById("search-dropdown-books");
  const journalsDd = document.getElementById("search-dropdown-journals");
  const articlesBase = articlesDd.href;
  const booksBase = booksDd.href;
  const journalsBase = journalsDd.href;

  searchInput.addEventListener('input', updateSearchers);

  function updateSearchers() {
    const queryString = this.value.trim() ? "&query=any,contains," + this.value.trim() : "";
    articlesDd.href = articlesBase + queryString;
    booksDd.href = booksBase + queryString;
    journalsDd.href = journalsBase + queryString;
  }
}
