<?php
session_start();
require_once 'config/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$userId = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT username, email, role FROM users WHERE id = ?");
$stmt->execute([$userId]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

$errorMessage = "";
$successMessage = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
    $currentPassword = $_POST['current_password'];
    $newPassword = $_POST['new_password'];
    $confirmPassword = $_POST['confirm_password'];

    if (empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {
        $errorMessage = "Все поля обязательны для заполнения!";
    } elseif ($newPassword !== $confirmPassword) {
        $errorMessage = "Новые пароли не совпадают!";
    } else {
        $stmt = $pdo->prepare("SELECT password FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        $storedPassword = $stmt->fetchColumn();

        if (!password_verify($currentPassword, $storedPassword)) {
            $errorMessage = "Неверный текущий пароль!";
        } else {
            $hashedPassword = password_hash($newPassword, PASSWORD_BCRYPT);
            $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
            $stmt->execute([$hashedPassword, $userId]);

            $successMessage = "Пароль успешно изменён!";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Личный кабинет</title>
    <link rel="stylesheet" href="assets/css/style.css?v=1.0">
</head>
<body>
<header class="header">
    <div class="pic">
        <span class="logo_text">Личный кабинет</span>
    </div>
    <nav class="nav_buttons">
        <button onclick="logout()" class="button">Выйти</button>
        <button onclick="redirectToPage(1)" class="button">Войти</button>
        <button onclick="redirectToPage(2)" class="button">Регистрация</button>
        <button onclick="redirectToPage(7)" class="button">Отчёт</button>
    </nav>
</header>

<main class="main_window">
<div class="navigation">
            <button  onclick="redirectToPage(3)" class="nav-button">Информация</button>
            <button  onclick="redirectToPage(4)" class="nav-button">Пользователи</button>
            <button  onclick="redirectToPage(5)" class="nav-button">На вывоз</button>
            <button  onclick="redirectToPage(6)" class="nav-button">О сайте</button>
        </div>
    <h1>Личный кабинет</h1>

    <?php if (!empty($errorMessage)): ?>
        <p class="error-message"><?php echo htmlspecialchars($errorMessage); ?></p>
    <?php endif; ?>
    
    <?php if (!empty($successMessage)): ?>
        <p class="success-message"><?php echo htmlspecialchars($successMessage); ?></p>
    <?php endif; ?>

    <div class="form-container">
        <h2>Информация о пользователе</h2>
        <p><strong>Имя пользователя:</strong> <?php echo htmlspecialchars($user['username']); ?></p>
        <p><strong>Электронная почта:</strong> <?php echo htmlspecialchars($user['email']); ?></p>
        <p><strong>Роль:</strong> <?php echo htmlspecialchars($user['role']); ?></p>
    </div>

    <div class="form-container">
        <h2>Изменить пароль</h2>
        <form method="POST" action="">
            <label for="current_password">Текущий пароль:</label>
            <input type="password" name="current_password" required>

            <label for="new_password">Новый пароль:</label>
            <input type="password" name="new_password" required>

            <label for="confirm_password">Подтвердите новый пароль:</label>
            <input type="password" name="confirm_password" required>

            <button type="submit" name="change_password">Изменить пароль</button>
        </form>
    </div>
</main>

<script>
function redirectToPage(num) {
    switch (num) {
            case 1: window.location.href = "login.php"; break;
            case 2: window.location.href = "register.php"; break;
            case 3: window.location.href = "dashboard.php"; break;
            case 4: window.location.href = "users.php"; break;
            case 5: window.location.href = "pickup.php"; break;
            case 6: window.location.href = "main.php"; break;
            case 7: window.location.href = "excel.php"; break;
            case 9: window.location.href = "profile.php"; break;
            default: console.error("Неверный номер страницы: " + num); break;
        }
}
</script>

</body>
</html>
