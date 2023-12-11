// connect to dept: library chat
const serviceURL =
  "https://chat-us.libanswers.com/widget_status?iid=450&rules=%5B%7B%22u%22%3A0%2C%22d%22%3A%5B1198%5D%2C%22c%22%3A%22%22%2C%22fallbackSeconds%22%3A0%7D%5D";

const checkInterval = 30000; // 30 seconds in milliseconds

// reload the iframe to show correct chatbox page
function reloadIframe() {
  document.getElementById("cw-iframe").src += "";
  console.log("iframe reload");
}

function setStyleProperty(status) {
  const root = document.documentElement;
  let name = status;
  console.log(name);
  root.style.setProperty("--cw-color-main", `var(--${name}-primary-color)`);
  root.style.setProperty("--cw-color-sematic", `var(--${name}-sematic-color)`);
  root.style.setProperty("--cw-color-text", `var(--${name}-text-color)`);
  root.style.setProperty("--cw-icon-default", `var(--${name}-icon-default)`);
  root.style.setProperty("--cw-icon-hover", `var(--${name}-icon-hover)`);
  root.style.setProperty("--cw-chevron-default", `var(--chevron-${name})`);
  root.style.setProperty(
    "--cw-color-background",
    `var(--${name}-background-color)`
  );
}

function updateStatusStyle(status) {
  let widgetStatus = document.getElementById("cw-status");
  if (status === true) {
    setStyleProperty("live");
    widgetStatus.innerText = "live";
    console.log("widget style updated to live");
  } else {
    setStyleProperty("offline");
    widgetStatus.innerText = "offline";
    console.log("widget style updated to offline");
  }
}

// check the service status
function checkServiceStatus() {
  fetch(serviceURL)
    //   check the server status and get the service status
    .then((response) => {
      if (response.status === 200) {
        console.log("server is live");
        console.log(response);
        let data = response.json();
        console.log(data);
        return data;
      } else {
        console.log("server is down");
      }
    })
    // update the chat widget based on the service status
    .then((data) => {
      const awayValue = data.away;
      if (typeof awayValue !== "undefined") {
        console.log("chat widget is live");
        updateStatusStyle(true);
      } else {
        console.log("chat widget is offline");
        updateStatusStyle(false);
        // reload the iframe to show correct chat widget page, only if the widget is already offline
        reloadIframe();
      }
    })
    .catch((error) => {
      console.log("Error:", error);
    });
}

// initial check
checkServiceStatus();

// set up a recurring check
setInterval(checkServiceStatus, checkInterval);

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
