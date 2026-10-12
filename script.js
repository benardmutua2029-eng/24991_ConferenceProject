// Student Technology and Innovation Conference - registration logic

// Fee rates in KSh (must match process.php)
var REGISTRATION_FEE = 1000;
var ATTENDANCE_PER_DAY = 500;
var WORKSHOP_FEE = 750;
var LUNCH_PER_DAY = 300;
var TSHIRT_FEE = 800;               // optional extra
var ACCOMMODATION_PER_NIGHT = 1500; // individual requirement (admission number ends in 1)

function formatKsh(amount) {
  return "KSh " + amount.toLocaleString("en-KE");
}

// Show workshop choices only for Workshop Participants
function showWorkshop() {
  var category = document.getElementById("category").value;
  var box = document.getElementById("workshopBox");
  if (category === "Workshop Participant") {
    box.classList.remove("hidden");
  } else {
    box.classList.add("hidden");
  }
}

// Enable the nights field only when accommodation is ticked
function toggleAccommodation() {
  var nights = document.getElementById("nights");
  nights.disabled = !document.getElementById("accommodation").checked;
  if (nights.disabled) {
    nights.value = 1;
    setError("nights", "");
  }
  calculateFee();
}

// Read a whole number safely
function readInt(id) {
  var value = parseInt(document.getElementById(id).value, 10);
  return isNaN(value) ? 0 : value;
}

// Calculate and display the total fee
function calculateFee() {
  var category = document.getElementById("category").value;
  var days = readInt("days");
  if (days < 1 || days > 3) { days = 0; }

  var registration = category === "" ? 0 : REGISTRATION_FEE;
  var attendance = days * ATTENDANCE_PER_DAY;
  var workshop = category === "Workshop Participant" ? WORKSHOP_FEE : 0;
  var meals = document.getElementById("meal").value === "Lunch" ? days * LUNCH_PER_DAY : 0;
  var tshirt = document.getElementById("tshirt").checked ? TSHIRT_FEE : 0;

  var accommodation = 0;
  if (document.getElementById("accommodation").checked) {
    var nights = readInt("nights");
    if (nights >= 1 && nights <= 3) { accommodation = nights * ACCOMMODATION_PER_NIGHT; }
  }

  var total = registration + attendance + workshop + meals + tshirt + accommodation;

  document.getElementById("s-reg").textContent = formatKsh(registration);
  document.getElementById("s-att").textContent = formatKsh(attendance);
  document.getElementById("s-ws").textContent = formatKsh(workshop);
  document.getElementById("s-meal").textContent = formatKsh(meals);
  document.getElementById("s-tshirt").textContent = formatKsh(tshirt);
  document.getElementById("s-acc").textContent = formatKsh(accommodation);
  document.getElementById("totalDisplay").textContent = formatKsh(total);
  document.getElementById("totalField").value = total;

  updatePackageMessage(category, days);
  return total;
}

// Message describing the selected package
function updatePackageMessage(category, days) {
  var msg = document.getElementById("packageMsg");
  if (category === "") {
    msg.textContent = "Select a participation category to see your package.";
  } else if (category === "Workshop Participant") {
    msg.textContent = "Package: Workshop Participant (" + document.getElementById("workshop").value + "), " + days + " day(s).";
  } else {
    msg.textContent = "Package: Conference Only, " + days + " day(s).";
  }
}

function setError(field, message) {
  var errorBox = document.getElementById("err-" + field);
  var wrapper = document.getElementById("f-" + field);
  if (errorBox) { errorBox.textContent = message; }
  if (wrapper) { wrapper.classList.toggle("invalid", message !== ""); }
}

// Validate the form; returns false to stop submission
function validateForm() {
  var valid = true;

  var fullname = document.getElementById("fullname").value.trim();
  setError("fullname", fullname === "" ? "Full name cannot be empty." : "");
  if (fullname === "") { valid = false; }

  var regno = document.getElementById("regno").value.trim();
  setError("regno", regno === "" ? "Enter your admission/registration number." : "");
  if (regno === "") { valid = false; }

  var email = document.getElementById("email").value.trim();
  var emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
  if (!emailPattern.test(email)) {
    setError("email", "Enter a valid email address, e.g. name@example.com.");
    valid = false;
  } else { setError("email", ""); }

  var phone = document.getElementById("phone").value.trim();
  if (!/^[0-9]{10}$/.test(phone)) {
    setError("phone", "Phone number must contain exactly 10 digits.");
    valid = false;
  } else { setError("phone", ""); }

  var days = readInt("days");
  if (days < 1 || days > 3) {
    setError("days", "Number of days must be between 1 and 3.");
    valid = false;
  } else { setError("days", ""); }

  if (document.getElementById("category").value === "") {
    setError("category", "Select a participation category.");
    valid = false;
  } else { setError("category", ""); }

  if (document.getElementById("accommodation").checked) {
    var nights = readInt("nights");
    if (nights < 1 || nights > 3) {
      setError("nights", "Nights must be between 1 and 3.");
      valid = false;
    } else { setError("nights", ""); }
  }

  if (valid) { calculateFee(); }
  return valid;
}

function resetAll() {
  document.getElementById("regForm").reset();
  var ids = ["fullname", "regno", "email", "phone", "category", "days", "nights"];
  for (var i = 0; i < ids.length; i++) { setError(ids[i], ""); }
  showWorkshop();
  toggleAccommodation();
}

// Live feedback on the phone field while typing
document.getElementById("phone").addEventListener("input", function () {
  this.value = this.value.replace(/[^0-9]/g, "");
});

calculateFee();
