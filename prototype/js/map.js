// js/map.js
document.addEventListener("DOMContentLoaded", function () {
  const campusLat = 37.9801987;
  const campusLng = 23.7350482;

  const map = L.map("map").setView([campusLat, campusLng], 15);

  L.tileLayer("https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png", {
    attribution: "© OpenStreetMap contributors",
  }).addTo(map);

  L.marker([campusLat, campusLng])
    .addTo(map)
    .bindPopup("University Campus")
    .openPopup();
});
