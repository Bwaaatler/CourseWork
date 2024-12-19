<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once 'config/db.php';

session_start();

$errorMessage = "";
$email = "";
$username = "";
$password = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $username = isset($_POST['username']) ? trim($_POST['username']) : '';
    $password = isset($_POST['password']) ? trim($_POST['password']) : '';

    if (empty($email) || empty($username) || empty($password)) {
        $errorMessage = "Все поля должны быть заполнены.";
    } else {
        try {
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE email = ? OR username = ?");
            $stmt->execute([$email, $username]);
            if ($stmt->fetchColumn()) {
                $errorMessage = "Пользователь с таким email или именем уже существует.";
            } else {
                $stmt = $pdo->query("SELECT COUNT(*) FROM users");
                $isFirstUser = $stmt->fetchColumn() == 0;
                $role = $isFirstUser ? 'Администратор' : 'Рабочий';

                $passwordHash = password_hash($password, PASSWORD_BCRYPT);
                $stmt = $pdo->prepare("INSERT INTO users (username, email, password_hash, role, status) VALUES (?, ?, ?, ?, ?)");
                if ($stmt->execute([$username, $email, $passwordHash, $role, '1'])) {
          
                    $userId = $pdo->lastInsertId();

                
                    $_SESSION['user_id'] = $userId;
                    $_SESSION['username'] = $username;
                    $_SESSION['role'] = $role;

                    header("Location: main.php");
                    exit();
                } else {
                    $errorMessage = "Ошибка при регистрации. Попробуйте позже.";
                }
            }
        } catch (PDOException $e) {
            $errorMessage = "Ошибка: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Регистрация</title>
    <link rel="stylesheet" href="assets/css/style.css?v=1.0">
</head>

<body>

<header class="header">
        <div class="pic">
            <span class="logo_text">Склад</span>
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
        <form method="POST" action="">
            <h1>Регистрация</h1>
            <label for="username">Имя пользователя:</label>
            <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($username); ?>" placeholder="Username" required>

            <label for="email">Email:</label>
            <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($email); ?>" placeholder="Email" required>

            <label for="password">Пароль:</label>
            <input type="password" id="password" name="password" placeholder="Password" required>

            <button type="submit">Зарегистрироваться</button>
        </form>

        <?php if (!empty($errorMessage)): ?>
            <p class="error-message"><?php echo htmlspecialchars($errorMessage); ?></p>
        <?php endif; ?>

    </div>
</main>

<script>
    var num;
    function redirectToPage(num) {
        switch(num){
         case 1:window.location.href = "login.php";break;
         case 2:window.location.href = "register.php";break;
         case 3:window.location.href = "dashboard.php";break;
         case 4:window.location.href = "users.php";break;
         case 5:window.location.href = "pickup.php";break;
         case 6:window.location.href = "main.php";break;
         case 7: window.location.href = "excel.php"; break;
         case 9: window.location.href = "profile.php"; break;
         default:console.error("Неверный номер страницы: " + num);break;
         }
    }
</script>

</body>
</html>
