<?php

declare(strict_types=1);

session_start();

if (isset($_POST["date-from"], $_POST["date-to"])) {

  $dateFrom = htmlspecialchars($_POST["date-from"]);
  $dateTo = htmlspecialchars($_POST["date-to"]);

  // Use only the day:
  $dateFrom = substr($dateFrom, -2);
  $dateTo = substr($dateTo, -2);

  // convert from str to int:
  $dateFrom = intval($dateFrom);
  $dateTo = intval($dateTo);


  // initialize db and get the chosen room and dates
  $db = new PDO("sqlite:../database/hotel.db");

  $statementCheckAvailability = $db->prepare(
    "SELECT * FROM occupancy
    WHERE date BETWEEN :dateFrom AND :dateTo
    AND room_id = 2" /* hard coded for now */
  );

  $statementCheckAvailability->bindParam(":dateFrom", $dateFrom, PDO::PARAM_INT);
  $statementCheckAvailability->bindParam(":dateTo", $dateTo, PDO::PARAM_INT);
  $statementCheckAvailability->execute();

  $chosenDatesAndRoom = $statementCheckAvailability->fetchAll(PDO::FETCH_ASSOC);


  // Check room availability
  $roomOccupancy = [];
  foreach ($chosenDatesAndRoom as $date) {
    $roomOccupancy[] = $date["occupied"]; // 1 or 0
  }

  if (in_array(true, $roomOccupancy)) {
    $_SESSION["message"] = "one or more dates are already booked.";
  } else {

    $statementMakeBooking = $db->prepare(
      "UPDATE occupancy
      SET occupied = 1
      WHERE date BETWEEN :dateFrom AND :dateTo
      AND room_id = 2" /* hard coded for now */
    );

    $statementMakeBooking->bindParam(":dateFrom", $dateFrom, PDO::PARAM_INT);
    $statementMakeBooking->bindParam(":dateTo", $dateTo, PDO::PARAM_INT);
    $statementMakeBooking->execute();

    $_SESSION["message"] = "booking succeded";
  }
}


header("Location: /index.php");