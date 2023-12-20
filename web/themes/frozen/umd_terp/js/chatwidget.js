// connect to dept: library chat
const serviceURL =
  "https://chat-us.libanswers.com/widget_status?iid=450&rules=%5B%7B%22u%22%3A0%2C%22d%22%3A%5B1198%5D%2C%22c%22%3A%22%22%2C%22fallbackSeconds%22%3A0%7D%5D";

const checkInterval = 30000; // 30 seconds in milliseconds

// reload the iframe to show correct chatbox page
function reloadIframe() {
  document.getElementById("cw-iframe").src += "";
  console.log("iframe reload");
}

// update chat widget's button style
function setStyleProperty(status) {
  const root = document.documentElement;
  let name = status;

  root.style.setProperty("--cw-color-main", `var(--${name}-primary-color)`);
  root.style.setProperty("--cw-color-sematic", `var(--${name}-sematic-color)`);
  root.style.setProperty("--cw-color-text", `var(--${name}-text-color)`);
  root.style.setProperty(
    "--cw-color-background",
    `var(--${name}-background-color)`
  );
  root.style.setProperty("--cw-icon-default", `var(--${name}-icon-default)`);
  root.style.setProperty("--cw-icon-hover", `var(--${name}-icon-hover)`);
  root.style.setProperty("--cw-chevron-default", `var(--chevron-${name})`);
}

// initiate style update based on the service status
function updateStatusStyle(status) {
  let widgetStatus = document.getElementById("cw-status");

  if (status === true) {
    setStyleProperty("live");
    widgetStatus.innerText = "live";
  } else {
    setStyleProperty("offline");
    widgetStatus.innerText = "offline";
  }
}

// check the service status
function checkServiceStatus() {
  fetch(serviceURL)
    //   check the server status and get the service status
    .then((response) => {
      if (response.status === 200) {
        let data = response.json();
        return data;
      } else {
        console.log("server is down");
      }
    })
    // update the chat widget based on the service status
    .then((data) => {
      const awayValue = data.away;
      if (typeof awayValue !== "undefined") {
        updateStatusStyle(true);
      } else {
        updateStatusStyle(false);
        // reload the iframe to show correct chat widget page, only if the widget is already offline
        reloadIframe();
      }
    })
    .catch((error) => {
      console.log("Chat Service Error:", error);
    });
}

// update chat service url to provide referer info
function updateIframeUrl(elementId) {
  let serviceSrc = document.getElementById(elementId);

  if (serviceSrc) {
    let currentUrl = document.URL;
    serviceSrc.setAttribute(
      "src",
      serviceSrc.getAttribute("src") + "?referer=" + currentUrl
    );
  }
}

// expand/collapse the chatbox
function expand() {
  let chatwidget = document.getElementById("chatwidget");
  let chevron = document.getElementById("cw-chevron");

  if (chatwidget.classList.contains("closed")) {
    chatwidget.classList.remove("closed");
    chevron.classList.remove("chevron-ver");
  } else {
    chatwidget.classList.add("closed");
    chevron.classList.add("chevron-ver");
  }
}

// open chat widown in a new tab for chat with us! button in the navigation bar
function openChatWindow() {
  let serviceSrc = document.getElementById("cw-iframe-window");

  if (serviceSrc) {
    window.open(
      serviceSrc.getAttribute("src"),
      "pop-up",
      "width=360, height=594"
    );
  } else {
    // fallback to the chat widget without referer info
    window.open(
      "https://umd.libanswers.com/chat/widget/5cffd49b55d69387be9a6fa51e3c5fa59efa09ca025ffc7367db9b7d083f17ec",
      "pop-up",
      "width=360, height=594"
    );
  }

  return false;
}

// initial check
checkServiceStatus();

// set up a recurring check
setInterval(checkServiceStatus, checkInterval);

// update chat service url to provide referer info
document.addEventListener("DOMContentLoaded", function () {
  updateIframeUrl("cw-iframe-window");
});
