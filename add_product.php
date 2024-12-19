<?php
session_start();


if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $name = trim($_POST['name']);
    $description = trim($_POST['description']);
    $quantity = (int)$_POST['quantity'];
    $zone = trim($_POST['zone']);

    $status = 'На складе'; 

    require_once 'config/db.php';

    try {
        $stmt = $pdo->prepare("INSERT INTO products (name, description, quantity, status, zone) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$name, $description, $quantity, $status, $zone]);

        echo "Товар успешно добавлен!";
    } catch (PDOException $e) {
        echo "Ошибка при добавлении товара: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Добавить товар</title>
    <link rel="stylesheet" href="assets/css/style.css?v=1.0">
</head>
<body>
<header class="header">
    <div class="pic">
        <span class="logo_text">Добавление</span>
    </div>
    <nav class="nav_buttons">
        <?php if (isset($_SESSION['user_id'])): ?>
    
            <button onclick="logout()" class="button">Выйти</button>
        <?php else: ?>
    
            <button onclick="redirectToPage(1)" class="button">Войти</button>
        <?php endif; ?>
        <button onclick="redirectToPage(2)" class="button">Регистрация</button>
        <button onclick="redirectToPage(7)" class="button">Отчёт</button>
        <button onclick="redirectToPage(9)" class="button">Личный кабинет</button>
    </nav>
</header>

<main class="main_window">
<div class="navigation">
        <button onclick="redirectToPage(3)" class="nav-button">Информация</button>
        <button onclick="redirectToPage(4)" class="nav-button">Пользователи</button>
        <button onclick="redirectToPage(5)" class="nav-button">На вывоз</button>
        <button onclick="redirectToPage(6)" class="nav-button">О сайте</button>
    </div>
    <div class="form-container">
        <h1>Добавить новый товар</h1>
        <form method="POST" action="">
            <label for="name">Название товара:</label>
            <input type="text" id="name" name="name" placeholder="Введите название товара" required>

            <label for="description">Описание товара:</label>
            <textarea id="description" name="description" placeholder="Введите описание товара" required></textarea>

            <label for="quantity">Количество:</label>
            <input type="number" id="quantity" name="quantity" placeholder="Введите количество товара" required min="1">

            <label for="zone">Зона:</label>
            <input type="text" id="zone" name="zone" placeholder="Введите зону" required>

            <button type="submit">Добавить товар</button>
        </form>
    </div>
</main>

<script>
    function logout() {
 
    window.location.href = "logout.php"; 
}
    var num;
    function redirectToPage(num) {
        switch(num) {
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