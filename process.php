<?php
// process.php - server-side processing and confirmation

// Rates in KSh (must match js/script.js)
const REGISTRATION_FEE = 1000;
const ATTENDANCE_PER_DAY = 500;
const WORKSHOP_FEE = 750;
const LUNCH_PER_DAY = 300;
const TSHIRT_FEE = 800;
const ACCOMMODATION_PER_NIGHT = 1500;

$allowedCategories = ["Conference Only", "Workshop Participant"];
$allowedWorkshops = ["Web Development", "Mobile App Development", "Data Science and AI", "Cybersecurity Basics"];
$allowedMeals = ["None", "Lunch"];

function clean($value) {
    return htmlspecialchars(trim($value), ENT_QUOTES, 'UTF-8');
}

$errors = [];

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    $errors[] = "Please submit the registration form first.";
} else {
    // Receive values
    $fullname  = trim($_POST["fullname"] ?? "");
    $regno     = trim($_POST["regno"] ?? "");
    $email     = trim($_POST["email"] ?? "");
    $phone     = trim($_POST["phone"] ?? "");
    $year      = (int)($_POST["year"] ?? 0);
    $category  = $_POST["category"] ?? "";
    $days      = (int)($_POST["days"] ?? 0);
    $workshop  = $_POST["workshop"] ?? "";
    $meal      = $_POST["meal"] ?? "None";
    $tshirt    = isset($_POST["tshirt"]);
    $accommodation = isset($_POST["accommodation"]);
    $nights    = $accommodation ? (int)($_POST["nights"] ?? 0) : 0;
    $submittedTotal = (int)($_POST["total"] ?? 0);

    // Essential values present
    if ($fullname === "" || $regno === "" || $email === "" || $phone === "" || $category === "") {
        $errors[] = "Some required fields are missing.";
    }
    // Server-side validation
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) { $errors[] = "Invalid email address."; }
    if (!preg_match('/^[0-9]{10}$/', $phone)) { $errors[] = "Phone number must have exactly 10 digits."; }
    if ($days < 1 || $days > 3) { $errors[] = "Conference days must be between 1 and 3."; }
    if ($year < 1 || $year > 5) { $errors[] = "Invalid year of study."; }
    if (!in_array($category, $allowedCategories, true)) { $errors[] = "Invalid participation category."; }
    if (!in_array($meal, $allowedMeals, true)) { $errors[] = "Invalid meal option."; }
    if ($category === "Workshop Participant" && !in_array($workshop, $allowedWorkshops, true)) {
        $errors[] = "Invalid workshop selection.";
    }
    if ($accommodation && ($nights < 1 || $nights > 3)) { $errors[] = "Nights must be between 1 and 3."; }

    if (empty($errors)) {
        // Independent fee calculation: the JavaScript total is never trusted
        $workshopFee = ($category === "Workshop Participant") ? WORKSHOP_FEE : 0;
        $mealFee = ($meal === "Lunch") ? $days * LUNCH_PER_DAY : 0;
        $tshirtFee = $tshirt ? TSHIRT_FEE : 0;
        $accommodationFee = $accommodation ? $nights * ACCOMMODATION_PER_NIGHT : 0;

        $total = REGISTRATION_FEE + ($days * ATTENDANCE_PER_DAY) + $workshopFee
               + $mealFee + $tshirtFee + $accommodationFee;
        $mismatch = ($submittedTotal !== $total);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Registration result</title>
  <link rel="stylesheet" href="css/style.css">
</head>
<body>
  <header class="site">
    <div class="bar">
      <span class="brand">STIC 2026</span>
      <nav><a href="index.html">Home</a><a href="register.html">Register</a></nav>
    </div>
  </header>
  <main>
<?php if (!empty($errors)): ?>
    <div class="card fail">
      <h1>Registration failed</h1>
      <ul>
        <?php foreach ($errors as $e): ?><li><?= clean($e) ?></li><?php endforeach; ?>
      </ul>
      <a class="btn" href="register.html">Back to the form</a>
    </div>
<?php else: ?>
    <div class="card success">
      <h1>REGISTRATION SUCCESSFUL</h1>
      <dl>
        <dt>Participant</dt><dd><?= clean($fullname) ?></dd>
        <dt>Admission Number</dt><dd><?= clean($regno) ?></dd>
        <dt>Email</dt><dd><?= clean($email) ?></dd>
        <dt>Phone</dt><dd><?= clean($phone) ?></dd>
        <dt>Year of Study</dt><dd>Year <?= $year ?></dd>
        <dt>Category</dt><dd><?= clean($category) ?></dd>
        <dt>Conference Days</dt><dd><?= $days ?></dd>
        <dt>Workshop</dt><dd><?= $category === "Workshop Participant" ? clean($workshop) : "None" ?></dd>
        <dt>Meals</dt><dd><?= $meal === "Lunch" ? "Yes (Lunch)" : "No" ?></dd>
        <dt>T-shirt</dt><dd><?= $tshirt ? "Yes" : "No" ?></dd>
        <dt>Accommodation</dt><dd><?= $accommodation ? $nights . " night(s)" : "No" ?></dd>
      </dl>
      <div class="total-line">TOTAL AMOUNT PAYABLE: KSh <?= number_format($total) ?></div>
      <?php if ($mismatch): ?>
        <p class="error">Note: the total shown on the form (KSh <?= number_format($submittedTotal) ?>) differed from the verified amount. The amount above was recalculated by the server.</p>
      <?php endif; ?>
      <p>Thank you for registering for the Student Technology and Innovation Conference.</p>
      <a class="btn secondary" href="index.html">Back to home</a>
    </div>
<?php endif; ?>
  </main>
  <footer>Student Technology and Innovation Conference 2026</footer>
</body>
</html>
