// open chat widown in a new tab for chat with us! button in the navigation bar
function openChatWindow() {
  let serviceSrc = document.getElementById("cw-iframe-window");

  if (serviceSrc) {
    window.open(
      serviceSrc.getAttribute("src"),
      "pop-up",
      "width=360, height=594",
    );
  } else {
    // fallback to the chat widget without referer info
    window.open(
      "https://umd.libanswers.com/chat/widget/5cffd49b55d69387be9a6fa51e3c5fa59efa09ca025ffc7367db9b7d083f17ec",
      "pop-up",
      "width=360, height=594",
    );
  }

  return false;
}

// chat widget w/ new design system
// expand/collapse the chatbox
function expand() {
  let chatwidget = document.getElementById("chatwidget");
  let chevron = document.getElementById("cw--chevron");
  let button = document.getElementById("cw-service-status");

  if (chatwidget.classList.contains("closed")) {
    chatwidget.classList.remove("closed");
    chevron.classList.remove("chevron-ver");
    button.setAttribute("aria-expanded", "true");
  } else {
    chatwidget.classList.add("closed");
    chevron.classList.add("chevron-ver");
    button.setAttribute("aria-expanded", "false");
  }
}

// connect to dept: library chat
const serviceURL =
  "https://chat-us.libanswers.com/widget_status?iid=450&rules=%5B%7B%22u%22%3A0%2C%22d%22%3A%5B1198%5D%2C%22c%22%3A%22%22%2C%22fallbackSeconds%22%3A0%7D%5D";

const checkInterval = 30000; // 30 seconds

// reload the iframe to show correct chatbox page
function reloadIframe() {
  addRefererToIframe();
}

// add referer to the chat widget iframe
function addRefererToIframe() {
  const iframe = document.getElementById("cw-iframe-window");
  if (!iframe) {
    return;
  }
  const referer = window.location.href;

  try {
    const url = new URL(iframe.src, window.location.href);
    if (url.searchParams.get("referer") !== referer) {
      url.searchParams.set("referer", referer);
      iframe.src = url.toString();
    }
  } catch (e) {
    if (!/[?&]referer=/.test(iframe.src)) {
      iframe.src =
        iframe.src +
        (iframe.src.indexOf("?") === -1 ? "?" : "&") +
        "referer=" +
        encodeURIComponent(referer);
    }
  }
}

// update the chat widget UI
function updateChatWidgetStatus(status) {
  let widget = document.getElementById("chatwidget");
  let widgetStatus = document.getElementById("cw--status");
  let button = document.getElementById("cw-service-status");

  if (!widget || !widgetStatus) {
    return;
  }

  if (status === true) {
    widgetStatus.innerText = "live";
    widget.classList.remove("offline");
    button.setAttribute(
      "aria-label",
      "Chat With Us! is online. Click to expand the chat widget.",
    );
    reloadIframe();
  } else {
    widgetStatus.innerText = "offline";
    widget.classList.add("offline");
    button.setAttribute(
      "aria-label",
      "Chat With Us! is offline. Click to expand the chat widget.",
    );
    reloadIframe();
  }
}

// check the service status
function checkServiceStatus() {
  return fetch(serviceURL) // RETURN the promise
    .then((response) => {
      if (!response.ok) {
        throw new Error(`Server returned status ${response.status}`);
      }
      return response.json();
    })
    .then((data) => {
      const isLive = data.away === false;
      updateChatWidgetStatus(isLive);
    })
    .catch((error) => {
      updateChatWidgetStatus(false);
    });
}

// Initialize on DOM ready
function initChatWidget() {
  // Initial check
  checkServiceStatus().then(() => {
    reloadIframe();
  });

  // Set up recurring check
  const intervalId = setInterval(() => {
    checkServiceStatus();
  }, checkInterval);
}

// Run initialization
if (document.readyState === "loading") {
  document.addEventListener("DOMContentLoaded", initChatWidget);
} else {
  initChatWidget();
}
