<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}


if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Администратор') {
    echo "Доступ запрещён!";
    exit();
}

require_once 'config/db.php';


$currentUserId = $_SESSION['user_id'];


$stmt = $pdo->query("SELECT id FROM users WHERE role = 'Администратор' LIMIT 1");
$adminUser = $stmt->fetch(PDO::FETCH_ASSOC);
$adminId = $adminUser ? $adminUser['id'] : null;


if (isset($_POST['delete_id'])) {
    $deleteId = $_POST['delete_id'];
    

    if ($deleteId !== $adminId && $deleteId !== $currentUserId) { 
        try {
            $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
            $stmt->execute([$deleteId]);
            header("Location: users.php"); 
            exit();
        } catch (PDOException $e) {
            $errorMessage = "Ошибка при удалении пользователя: " . $e->getMessage();
            echo "<script>alert('$errorMessage');</script>";
        }
    } else {
        echo "<script>alert('Невозможно удалить себя или администратора!');</script>";
    }
}

if (isset($_POST['toggle_status_id'])) {
    $toggleId = $_POST['toggle_status_id'];
    
    if ($toggleId !== $currentUserId && $toggleId !== $adminId) {
        try {

            $stmt = $pdo->prepare("SELECT status FROM users WHERE id = ?");
            $stmt->execute([$toggleId]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user) {
            
                $newStatus = ($user['status'] == 1) ? 0 : 1;

     
                $stmt = $pdo->prepare("UPDATE users SET status = ? WHERE id = ?");
                $stmt->execute([$newStatus, $toggleId]);
                header("Location: users.php"); 
                exit();
            }
        } catch (PDOException $e) {
            $errorMessage = "Ошибка при изменении статуса пользователя: " . $e->getMessage();
            echo "<script>alert('$errorMessage');</script>";
        }
    } else {
        echo "<script>alert('Невозможно изменить статус своего аккаунта или администратора!');</script>";
    }
}

$stmt = $pdo->query("SELECT * FROM users");
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>



<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Пользователи</title>
    <link rel="stylesheet" href="assets/css/style.css?v=1.0">
    <style>
        table.user-table {
            width: 100%;
            border-collapse: collapse;
        }

        table.user-table th, table.user-table td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }

        table.user-table th {
            cursor: pointer;
            background-color: #f2f2f2;
        }

        .delete-button, .toggle-status-button {
            color: red;
            background-color: transparent;
            border: none;
            cursor: pointer;
        }

        .toggle-status-button {
            color: green;
        }

        .add-button {
            margin-top: 20px;
            padding: 10px 20px;
            background-color: green;
            color: white;
            border: none;
            cursor: pointer;
        }

        .add-button:hover {
            background-color: darkgreen;
        }
    </style>
</head>
<body>
<header class="header">
    <div class="pic">
        <span class="logo_text">Администрирование</span>
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
    <section class="content">
        <h1>Управление пользователями</h1>

        <?php if (!empty($errorMessage)): ?>
            <p class="error-message"><?= htmlspecialchars($errorMessage) ?></p>
        <?php endif; ?>

        <table class="user-table">
            <thead>
                <tr>
                    <th>Имя</th>
                    <th>Электронная почта</th>
                    <th>Роль</th>
                    <th>Статус</th>
                    <th>Действия</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($users) > 0): ?>
                    <?php foreach ($users as $user): ?>
                        <tr>
                            <td><?= htmlspecialchars($user['username']) ?></td>
                            <td><?= htmlspecialchars($user['email']) ?></td>
                            <td><?= htmlspecialchars($user['role']) ?></td>
                            <td><?= htmlspecialchars($user['status'] == 1 ? 'Активен' : 'Заблокирован') ?></td>
                            <td>
                                <?php if ($user['id'] !== $currentUserId && $user['id'] !== $adminId): ?> 
                                    <form method="POST" action="" class="inline-form" onsubmit="return confirmDelete()">
                                        <input type="hidden" name="delete_id" value="<?= $user['id'] ?>">
                                        <button type="submit" class="delete-button">Удалить</button>
                                    </form>
                                    <form method="POST" action="" class="inline-form" onsubmit="return confirmToggle()">
                                        <input type="hidden" name="toggle_status_id" value="<?= $user['id'] ?>">
                                        <button type="submit" class="toggle-status-button"><?= $user['status'] == 1 ? 'Заблокировать' : 'Разблокировать' ?></button>
                                    </form>
                                <?php else: ?>
                                    <span><?= $user['id'] == $adminId ? 'Администратор' : 'Ваш аккаунт' ?></span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5">Пользователи не найдены</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </section>
</main>

<script>
    function confirmDelete() {
        return confirm("Вы уверены, что хотите удалить этого пользователя?");
    }

    function confirmToggle() {
        return confirm("Вы уверены, что хотите изменить статус этого пользователя?");
    }
    function logout() {

    window.location.href = "logout.php"; 
}
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
