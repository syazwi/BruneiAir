<?php
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}
?>

<nav class="bg-red-600 p-4 text-white">
    <div class="container mx-auto flex justify-between items-center">
        <a href="dashboard.php" class="text-xl font-bold no-underline">Brunei Air</a>
        <ul class="flex space-x-6">
            <li><a href="flights.php" class="font-bold no-underline">Flights</a></li>
            <li><a href="bookings.php" class="font-bold no-underline">Bookings</a></li>
            <li><a href="crew.php" class="font-bold no-underline">Crew</a></li>
            <li><a href="aircraft.php" class="font-bold no-underline">Aircraft</a></li>
        </ul>
        <a href="logout.php" class="bg-black px-4 py-2 rounded-lg hover:bg-gray-900 text-white font-bold">Logout</a>
    </div>
</nav>
