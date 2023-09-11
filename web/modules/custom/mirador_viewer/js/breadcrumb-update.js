function updateBreadcrumb() {
  let text = document.getElementById("collection-link").innerText;
  let link = document.getElementById("collection-link").href;

  const collection = document.getElementById("breadcrumb-collection");

  if (link && text) {
    collection.href = link;
    collection.innerText = "Collection: " + text;
  }
}

updateBreadcrumb();
