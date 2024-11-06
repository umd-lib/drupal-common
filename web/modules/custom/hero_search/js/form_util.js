document.addEventListener("DOMContentLoaded", function () {
  // Initialize search functionality
  initializeSearch();

  // Initialize tabs functionality
  initializeTabs();
});

function initializeSearch() {
  const searchInput = document.getElementById("search-query-input-discover");
  const dropdowns = {
    articles: {
      element: document.getElementById("search-dropdown-articles"),
      baseUrl: "",
    },
    books: {
      element: document.getElementById("search-dropdown-books"),
      baseUrl: "",
    },
    journals: {
      element: document.getElementById("search-dropdown-journals"),
      baseUrl: "",
    },
  };

  // Store base URLs
  Object.values(dropdowns).forEach((dropdown) => {
    if (dropdown.element) {
      dropdown.baseUrl = dropdown.element.href;
    }
  });

  // Add event listener to search input
  if (searchInput) {
    searchInput.addEventListener("input", function () {
      const queryString = this.value.trim()
        ? `&query=any,contains,${encodeURIComponent(this.value.trim())}`
        : "";

      // Update all dropdown URLs
      Object.values(dropdowns).forEach((dropdown) => {
        if (dropdown.element) {
          dropdown.element.href = dropdown.baseUrl + queryString;
        }
      });
    });
  }
}

function initializeTabs() {
  const tabList = document.querySelector('[role="tablist"]');
  const tabs = document.querySelectorAll('[role="tab"]');
  const panels = document.querySelectorAll('[role="tabpanel"]');
  let currentTabIndex = 0;

  if (!tabList || !tabs.length || !panels.length) return;

  // Handle click events
  tabList.addEventListener("click", (e) => {
    const target = e.target.closest('[role="tab"]');
    if (!target) return;

    const newIndex = Array.from(tabs).indexOf(target);
    if (newIndex !== currentTabIndex) {
      switchTab(target);
      currentTabIndex = newIndex;
    }
  });

  // Handle keyboard navigation
  tabList.addEventListener("keydown", (e) => {
    const target = e.target.closest('[role="tab"]');
    if (!target) return;

    const currentIdx = Array.from(tabs).indexOf(target);
    let nextTab;

    const keyActions = {
      ArrowLeft: () => (currentIdx === 1 ? tabs[0] : target),
      ArrowRight: () => (currentIdx === 0 ? tabs[1] : target),
      Home: () => tabs[0],
      End: () => tabs[1],
    };

    if (keyActions[e.key]) {
      nextTab = keyActions[e.key]();
      if (nextTab !== target) {
        e.preventDefault();
        switchTab(nextTab);
        nextTab.focus();
        currentTabIndex = Array.from(tabs).indexOf(nextTab);
      }
    }
  });

  function switchTab(newTab) {
    if (!newTab) return;

    // Update tabs state
    tabs.forEach((tab) => {
      const isSelected = tab === newTab;
      tab.setAttribute("aria-selected", isSelected.toString());
      tab.setAttribute("tabindex", isSelected ? "0" : "-1");
      tab.classList.toggle("active", isSelected);
    });

    // Update panels state
    const targetPanelId = newTab.getAttribute("aria-controls");
    panels.forEach((panel) => {
      const isTarget = panel.id === targetPanelId;
      panel.setAttribute("aria-hidden", (!isTarget).toString());
      panel.classList.toggle("active", isTarget);
    });
  }

  // Initialize first tab
  switchTab(tabs[0]);
  currentTabIndex = 0;
}
