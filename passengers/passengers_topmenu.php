<?php


// Retrieve the logged-in passenger's username
$userStmt = $pdo->prepare("SELECT username FROM Users WHERE user_id = ?");
$userStmt->execute([$_SESSION['user_id']]);
$userInfo = $userStmt->fetch(PDO::FETCH_ASSOC);
$username = $userInfo['username'];
?>

<nav class="bg-red-600 p-4 text-white">
  <div class="container mx-auto flex justify-between items-center">
    <a href="passengers_dashboard.php" class="text-xl font-bold no-underline">Brunei Air</a>
    <ul class="flex space-x-6">
      <li><a href="passengers_dashboard.php" class="font-bold no-underline">Dashboard</a></li>
      <li><a href="my_bookings.php" class="font-bold no-underline">My Bookings</a></li>
      <li><a href="view_flights.php" class="font-bold no-underline">View Flights</a></li>

    </ul>
    <div class="flex items-center space-x-4">
      <div class="flex items-center">
  
        <span class="font-bold"><?= htmlspecialchars($username) ?></span>
      </div>
      <a href="logout.php" class="bg-black px-4 py-2 rounded-lg hover:bg-gray-900 text-white font-bold">Logout</a>
    </div>
  </div>
</nav>
