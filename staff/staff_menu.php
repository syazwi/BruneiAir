<?php
if (!isset($_SESSION['user_id'])) {
    header("Location: staff_login.php");
    exit();
}
require '../config.php';

// Retrieve the logged-in user's name
$userStmt = $pdo->prepare("SELECT username FROM Users WHERE user_id = ?");
$userStmt->execute([$_SESSION['user_id']]);
$userInfo = $userStmt->fetch(PDO::FETCH_ASSOC);
$username = $userInfo['username'];
?>

<nav class="bg-red-600 p-4 text-white">
  <div class="container mx-auto flex justify-between items-center">
    <a href="staff_dashboard.php" class="text-xl font-bold no-underline">Brunei Air</a>
    <ul class="flex space-x-6">
      <li><a href="staff_dashboard.php" class="font-bold no-underline">Dashboard</a></li>
      <li><a href="flights.php" class="font-bold no-underline">Flights</a></li>
      <li><a href="bookings.php" class="font-bold no-underline">Bookings</a></li>
      <li><a href="crew.php" class="font-bold no-underline">Crew</a></li>
      <li><a href="aircraft.php" class="font-bold no-underline">Aircraft</a></li>
    </ul>
    <div class="flex items-center space-x-4">
        <!-- Person logo with user's name -->
        <div class="flex items-center">
        
            <span class="font-bold"><?= htmlspecialchars($username) ?></span>
        </div>
        <a href="../logout.php" class="bg-black px-4 py-2 rounded-lg hover:bg-gray-900 text-white font-bold">Logout</a>
    </div>
  </div>
</nav>
