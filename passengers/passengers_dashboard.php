<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'passenger') {
    header("Location: passengers_login.php");
    exit();
}
require '../config.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Passenger Dashboard - Brunei Airlines</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
  <?php include 'passengers_topmenu.php'; ?>

  <div class="container mx-auto mt-10 p-6 bg-white shadow-lg rounded-lg">
    <h2 class="text-3xl font-bold text-center text-red-600">Welcome to Your Dashboard</h2>
    <p class="text-center mt-2 text-gray-700">Manage your bookings, view flight details, and update your profile.</p>

    <div class="grid grid-cols-2 gap-6 mt-6">
      <a href="view_flights.php" class="block bg-red-600 text-white p-4 rounded-lg text-center hover:bg-red-700 font-bold">
        View Flights
      </a>
      <a href="my_bookings.php" class="block bg-black text-white p-4 rounded-lg text-center hover:bg-gray-900 font-bold">
        My Bookings
      </a>
   
    </div>
  </div>


  <br><br><br><br><br><br>
  <br><br><br><br><br><br>
  <br><br>  <br><br><br>

  <?php include '../footer.php'; ?>
</body>
</html>
