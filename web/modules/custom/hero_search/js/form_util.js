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

    // Updated key actions to support multiple tabs
    const keyActions = {
      ArrowLeft: () => {
        // Move to previous tab, wrap to end if at start
        const newIndex = currentIdx === 0 ? tabs.length - 1 : currentIdx - 1;
        return tabs[newIndex];
      },
      ArrowRight: () => {
        // Move to next tab, wrap to start if at end
        const newIndex = currentIdx === tabs.length - 1 ? 0 : currentIdx + 1;
        return tabs[newIndex];
      },
      Home: () => tabs[0], // First tab
      End: () => tabs[tabs.length - 1], // Last tab
      ArrowDown: () => {
        // Same as ArrowRight for horizontal tabs
        const newIndex = currentIdx === tabs.length - 1 ? 0 : currentIdx + 1;
        return tabs[newIndex];
      },
      ArrowUp: () => {
        // Same as ArrowLeft for horizontal tabs
        const newIndex = currentIdx === 0 ? tabs.length - 1 : currentIdx - 1;
        return tabs[newIndex];
      },
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
