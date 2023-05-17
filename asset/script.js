// Fonction affichage

function toggleForm(id) {
  var form = document.getElementById(id);
  if (form.style.display === "none") {
    form.style.display = "block";
  } else {
    form.style.display = "none";
  }
}

function showNotification(message) {
  // Création de la notification
  var notification = document.createElement("div");
  notification.className = "notification";
  notification.textContent = message;
  // Ajout du style
  notification.style.position = "fixed";
  notification.style.top = "10px";
  notification.style.right = "10px";
  notification.style.backgroundColor = "#FFD600";
  notification.style.color = "white";
  notification.style.zIndex = "9999";

  // ajout de la notification
  document.body.appendChild(notification);

  // suppression de la notification après 5 secondes
  setTimeout(function () {
    document.body.removeChild(notification);
  }, 5000);
}










