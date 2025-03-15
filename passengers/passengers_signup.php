<?php
session_start();
require '../config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Check if username already exists
    $stmt = $pdo->prepare("SELECT * FROM Users WHERE username = ?");
    $stmt->execute([$username]);
    if ($stmt->fetch()) {
        $error = "Username already exists!";
    } else {
        // Insert new passenger with role 'passenger'
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO Users (username, password_hash, role) VALUES (?, ?, ?)");
        if ($stmt->execute([$username, $hashed_password, 'passenger'])) {
            header("Location: passengers_login.php?signup=success");
            exit();
        } else {
            $error = "Signup failed. Please try again.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Passenger Sign Up - Brunei Airlines</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="flex items-center justify-center h-screen bg-gray-100">
  <div class="bg-white p-8 rounded-lg shadow-lg w-120">
    <h2 class="text-2xl font-bold mb-4 text-center text-red-600">Brunei Airlines | Passenger Sign Up</h2>
    
    <?php if (!empty($error)): ?>
      <p class="text-red-500 text-center"><?= $error ?></p>
    <?php endif; ?>
    
    <form method="POST" class="space-y-4">
      <div>
        <label class="block text-gray-700">Username</label>
        <input type="text" name="username" required class="w-full p-2 border border-gray-300 rounded-lg">
      </div>
      
      <div>
        <label class="block text-gray-700">Password</label>
        <input type="password" name="password" required class="w-full p-2 border border-gray-300 rounded-lg">
      </div>
      
      <button type="submit" class="w-full bg-red-600 text-white py-2 rounded-lg">Sign Up</button>
    </form>
    
    <p class="mt-4 text-center">
      Already have an account? 
      <a href="passengers_login.php" class="text-red-500 hover:underline">Login</a>
    </p>
  </div>
</body>
</html>
