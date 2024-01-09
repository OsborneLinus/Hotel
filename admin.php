<?php

declare(strict_types=1);

require __DIR__ . "/php/autoload.php";
require __DIR__ . "/php/header.php";
require(__DIR__ . '/vendor/autoload.php');

use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

if (isset($_POST["admin-name"], $_POST["admin-password"])) {

  if ($_POST["admin-password"] === $_ENV["API_KEY"]) {
    $_SESSION["loggedIn"] = true;
  } else {
    $_SESSION["loginMessage"] = "Incorrect name or password";
  }
}

if (empty($_SESSION["loggedIn"])) {
  header("Location: /login.php");
}

/* code from calender.php to print calender on admin page: */
$week = [
  "Monday",
  "Tuesday",
  "Wednesday",
  "Thursday",
  "Friday",
  "Saturday",
  "Sunday"
];

if (isset($_POST["roomType"])) {
  $_SESSION["roomType"] = intval($_POST["roomType"]);
} else {
  $_SESSION["roomType"] = 3; // === deluxe, the default choice
}

$roomChosen = $_SESSION["roomType"];

// Get info on the chosen room:
$statementGetRoomInfo = $db->prepare(
  "SELECT name, base_price FROM rooms
  WHERE id = :id"
);
$statementGetRoomInfo->bindParam(":id", $roomChosen, PDO::PARAM_INT);
$statementGetRoomInfo->execute();
$roomInfo = $statementGetRoomInfo->fetch(PDO::FETCH_ASSOC);


// Get data from table reservations
$reservations = getDataFromDb("reservations", $roomChosen);

$reservedDates = filterDatesFromData($reservations);

// Get data from table bookings
$bookings = getDataFromDb("bookings", $roomChosen);

$bookedDates = filterDatesFromData($bookings);


// filter out reservations that doesn't have a matching booking
$filteredReservations = array_filter($reservations, function ($r) use ($bookedDates) {

  $range = range($r["checkin_date"], $r["checkout_date"], 1);

  if (array_diff($range, $bookedDates)) {
    return true;
  }
});


$timeOfPageLoad = time();
$tenMinutesAgo =  $timeOfPageLoad - 600; // a reservation is hold for 10 minutes

// delete old reservations that don't have a matching booking
foreach ($filteredReservations as $filteredItem) {
  $statementDeleteOldReservations = $db->prepare(
    "DELETE FROM reservations WHERE timestamp < :tenMinutesAgo AND id = :id"
  );
  $statementDeleteOldReservations->bindParam(":tenMinutesAgo", $tenMinutesAgo, PDO::PARAM_INT);
  $statementDeleteOldReservations->bindParam(":id", $filteredItem["id"], PDO::PARAM_INT);
  $statementDeleteOldReservations->execute();
}

// select reservations again after clearing old ones
$reservationsUpdated = getDataFromDb("reservations", $roomChosen);

$reservedDatesUpdated = filterDatesFromData($reservationsUpdated);
/* code from calender.php ends here */

?>

<div class="mx-auto pb-16 px-4 sm:px-6 lg:px-8">

  <div id="content-container" class="bg-cyan-50 p-4 lg:p-8 mx-auto grid max-w-md lg:max-w-3xl rounded-3xl shadow-cyan-50/25 shadow-xl">

    <!-- calender to show occupancy: -->
    <h2 class="mb-8 text-center text-2xl font-extrabold leading-relaxed">Room occupancy</h2>

    <form action="admin.php" method="post" class="mb-8 mx-auto grid max-w-md grid-cols-3 gap-8 lg:mx-0 lg:max-w-none lg:grid-cols-3">
      <button name="roomType" type="submit" value="1" class="font-bold text-blue-50 bg-blue-950 rounded p-2 shadow-blue-950/25 shadow-xl hover:bg-blue-900 <?= ($roomChosen === 1) ? "ring-blue-400 ring-8" : "" ?>">Economy</button>
      <button name="roomType" type="submit" value="2" class="font-bold text-purple-50 bg-purple-950 rounded p-2 shadow-purple-950/25 shadow-xl hover:bg-purple-900 <?= ($roomChosen === 2) ? "ring-purple-400 ring-8" : "" ?>">Standard</button>
      <button name="roomType" type="submit" value="3" class="font-bold text-yellow-50 bg-yellow-950 rounded p-2 shadow-yellow-950/25 shadow-xl hover:bg-yellow-900 <?= ($roomChosen === 3) ? "ring-yellow-400 ring-8" : "" ?>">Deluxe</button>
    </form>

    <div class="mb-8 mx-auto grid gap-1 grid-cols-7 w-full">
      <div class="text-center text-base font-bold">Mon</div>
      <div class="text-center text-base font-bold">Tue</div>
      <div class="text-center text-base font-bold">Wed</div>
      <div class="text-center text-base font-bold">Thu</div>
      <div class="text-center text-base font-bold">Fri</div>
      <div class="text-center text-base font-bold">Sat</div>
      <div class="text-center text-base font-bold">Sun</div>

      <?php

      // Create month with 31 days and week starting on the 1st (Jan 2024)
      // The counter helps to assign the correct weekday to each date

      $counter = 0;

      for ($i = 1; $i < 32; $i++) :

        $isReserved = in_array($i, $reservedDatesUpdated);
        $isBooked = in_array($i, $bookedDates);


        if ($roomChosen === 1) {
          $buttonClass = $isBooked ? "bg-blue-950 cursor-not-allowed"
            : ($isReserved  ? "bg-blue-800 cursor-not-allowed" : "bg-blue-600");
        } else if ($roomChosen === 2) {
          $buttonClass = $isBooked ? "bg-purple-950 cursor-not-allowed"
            : ($isReserved  ? "bg-purple-800 cursor-not-allowed" : "bg-purple-600");
        } else if ($roomChosen === 3) {
          $buttonClass = $isBooked ? "bg-yellow-950 cursor-not-allowed"
            : ($isReserved  ? "bg-yellow-800 cursor-not-allowed" : "bg-yellow-600");
        }

        $disabledStatus = $isReserved ? "disabled" : "";

        $weekday;

        if (isset($week[$counter])) {
          $weekday = $week[$counter];
        } else {
          $weekday = "Monday";
          $counter = 0;
        }

        $counter++; ?>

        <button <?= $disabledStatus ?> value="<?= $i ?>" id="calender-day-<?= $i ?>" class="calender-day py-4 text-base font-bold rounded-sm text-cyan-50 <?= "{$weekday} {$buttonClass}"; ?>">
          <?= $i ?>
        </button>

      <?php endfor; ?>

    </div>

    <div class="h-1 w-full bg-cyan-950 my-4"></div> <!-- separator -->

    <!-- Forms to update room prices: -->
    <h2 class="mb-8 text-center text-2xl font-extrabold leading-relaxed">Room prices</h2>

    <form action="admin.php" method="post" class="flex flex-col text-base font-bold leading-loose mb-8">
      <div>Room name: X</div>
      <div>Current price: Y</div>
      <label for="new-price">New price for X:</label>
      <div class="flex items-center gap-4">
        <input type="number" name="new-price" placeholder="X" class="w-32 border-0 ring-1 ring-inset ring-gray-600 focus:ring-2 focus:ring-inset focus:ring-cyan-950 placeholder:text-gray-300">
        <button type="submit" class="button-cyan">Update</button>
      </div>
    </form>

    <form action="admin.php" method="post" class="flex flex-col text-base font-bold leading-loose mb-8">
      <div>Room name: X</div>
      <div>Current price: Y</div>
      <label for="new-price">New price for X:</label>
      <div class="flex items-center gap-4">
        <input type="number" name="new-price" placeholder="X" class="w-32 border-0 ring-1 ring-inset ring-gray-600 focus:ring-2 focus:ring-inset focus:ring-cyan-950 placeholder:text-gray-300">
        <button type="submit" class="button-cyan">Update</button>
      </div>
    </form>

    <form action="admin.php" method="post" class="flex flex-col text-base font-bold leading-loose mb-8">
      <div>Room name: X</div>
      <div>Current price: Y</div>
      <label for="new-price">New price for X:</label>
      <div class="flex items-center gap-4 justify-start">
        <input type="number" name="new-price" placeholder="X" class="w-32 border-0 ring-1 ring-inset ring-gray-600 focus:ring-2 focus:ring-inset focus:ring-cyan-950 placeholder:text-gray-300">
        <button type="submit" class="button-cyan">Update</button>
      </div>
    </form>

    <div class="h-1 w-full bg-cyan-950 mt-4 mb-8"></div> <!-- separator -->

    <div class="flex flex-col lg:flex-row gap-8 justify-between ">
      <div class="text-center font-bold  text-2xl bg-cyan-950 text-cyan-50 px-4 py-2">
        Account balance: $<span class="text-cyan-50" id="total-price">000</span>
      </div>

      <form id="form-logout" action="/login.php" method="post" class="text-base font-bold leading-loose bg-cyan-50 mx-auto lg:mr-0 lg:ml-auto max-w-md rounded-3xl shadow-cyan-50/25 shadow-xl">
        <button type="submit" class="button-cyan">Logout</button>
      </form>

    </div>
  </div>

  <?php

  // echo "<pre class='font-bold text-xl mx-auto bg-cyan-50 p-8 mt-8'>";
  // echo "session:\n";
  // print_r($_SESSION);
  // echo "post:\n";
  // print_r($_POST);
  // echo "</pre>";

  ?>

</div>
</body>

</html>
