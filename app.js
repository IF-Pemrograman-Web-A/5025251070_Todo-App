if ("serviceWorker" in navigator) {
  window.addEventListener("load", function() {
    navigator.serviceWorker.register("sw.js");
  });
}

let deferredPrompt;
const btnInstall = document.getElementById("btn-install");

window.addEventListener("beforeinstallprompt", function(e) {
  e.preventDefault();
  deferredPrompt = e;
  if (btnInstall) {
    btnInstall.style.display = "inline-block";
  }
});

if (btnInstall) {
  btnInstall.addEventListener("click", function() {
    if (deferredPrompt) {
      deferredPrompt.prompt();
      deferredPrompt.userChoice.then(function(choiceResult) {
        if (choiceResult.outcome === "accepted") {
          btnInstall.style.display = "none";
        }
        deferredPrompt = null;
      });
    }
  });
}

window.addEventListener("appinstalled", function() {
  if (btnInstall) {
    btnInstall.style.display = "none";
  }
  deferredPrompt = null;
});
