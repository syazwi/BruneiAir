<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}
require 'config.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Brunei Airlines</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">

    <?php include 'topmenu.php'; ?>

    <div class="container mx-auto mt-10 p-6 bg-white shadow-lg rounded-lg">
        <h2 class="text-3xl font-bold text-center text-red-600">Brunei Airlines</h2>
        <p class="text-center mt-2 text-gray-700">Manage flights, bookings, crew, and aircraft efficiently.</p>

        <!-- Flight Attendant Image -->
        <div class="mt-4">
            <img src="img/flight_attendant.png" alt="Flight Attendant" class="w-1/2 mx-auto rounded-lg shadow-md">
        </div>

        <div class="grid grid-cols-2 gap-6 mt-6">
            <a href="flights.php" class="block bg-red-600 text-white p-4 rounded-lg text-center hover:bg-red-700 font-bold">Manage Flights</a>
            <a href="bookings.php" class="block bg-black text-white p-4 rounded-lg text-center hover:bg-gray-900 font-bold">Manage Bookings</a>
            <a href="crew.php" class="block bg-red-600 text-white p-4 rounded-lg text-center hover:bg-red-700 font-bold">Manage Crew</a>
            <a href="aircraft.php" class="block bg-black text-white p-4 rounded-lg text-center hover:bg-gray-900 font-bold">Manage Aircraft</a>
        </div>
    </div>

    <br><br><br><br><br><br>
    <?php include 'footer.php'; ?>

</body>
</html>
