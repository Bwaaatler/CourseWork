<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

require_once 'config/db.php';

$orderBy = 'name'; 
$orderDirection = 'ASC'; 

$minQuantity = isset($_GET['min_quantity']) ? (int)$_GET['min_quantity'] : 0;
$maxQuantity = isset($_GET['max_quantity']) ? (int)$_GET['max_quantity'] : 999999;

if (isset($_GET['sort_by'])) {
    $orderBy = $_GET['sort_by'];
}

if (isset($_GET['order_direction']) && ($_GET['order_direction'] === 'ASC' || $_GET['order_direction'] === 'DESC')) {
    $orderDirection = $_GET['order_direction'];
}

$searchQuery = "";
if (isset($_GET['search']) && !empty($_GET['search'])) {
    $searchQuery = trim($_GET['search']);
}

$sql = "SELECT id, name, description, quantity, status, zone 
        FROM products 
        WHERE name LIKE ? OR description LIKE ? 
        AND quantity BETWEEN ? AND ?
        ORDER BY $orderBy $orderDirection";

$stmt = $pdo->prepare($sql);
$stmt->execute(['%' . $searchQuery . '%', '%' . $searchQuery . '%', $minQuantity, $maxQuantity]);
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (isset($_POST['delete_id'])) {
    $deleteId = $_POST['delete_id'];
    try {
        $stmt = $pdo->prepare("DELETE FROM products WHERE id = ?");
        $stmt->execute([$deleteId]);
        $message = "Товар удалён!";
    } catch (PDOException $e) {
        $errorMessage = "Ошибка при удалении товара: " . $e->getMessage();
    }
}

foreach ($products as $product) {
    if ($product['quantity'] == 0) {
        try {
            $stmt = $pdo->prepare("DELETE FROM products WHERE id = ?");
            $stmt->execute([$product['id']]);
        } catch (PDOException $e) {
            $errorMessage = "Ошибка при удалении товара: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Склад товаров</title>
    <link rel="stylesheet" href="assets/css/style.css?v=1.0">
    <style>
        /* Стили для таблицы */
        table.product-table {
            width: 100%;
            border-collapse: collapse;
        }

        table.product-table th, table.product-table td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }

        table.product-table th {
            cursor: pointer;
            background-color: #f2f2f2;
        }

        /* Стили для кнопки удаления */
        .delete-button {
            color: red;
            background-color: transparent;
            border: none;
            cursor: pointer;
        }

        /* Стили для кнопки добавления товара */
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

        .search-form input, .filter-form input {
            padding: 5px;
            margin-right: 10px;
        }

        .filter-form button, .search-form button {
            padding: 5px;
        }
    </style>
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
        <button onclick="redirectToPage(8)" class="button">Отчёт</button>
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
        <h1>Склад товаров</h1>

        <form method="GET" action="" class="search-form">
            <input type="text" name="search" placeholder="Поиск товара..." value="<?= htmlspecialchars($searchQuery) ?>">
            <button type="submit">Найти</button>
        </form>

        <form method="GET" action="" class="filter-form">
            <label for="min_quantity">Мин. количество:</label>
            <input type="number" name="min_quantity" id="min_quantity" value="<?= htmlspecialchars($minQuantity) ?>">
            
            <label for="max_quantity">Макс. количество:</label>
            <input type="number" name="max_quantity" id="max_quantity" value="<?= htmlspecialchars($maxQuantity) ?>">
            
            <button type="submit">Применить фильтры</button>
        </form>

        <?php if (!empty($message)): ?>
            <p class="success-message"><?= htmlspecialchars($message) ?></p>
        <?php endif; ?>
        <?php if (!empty($errorMessage)): ?>
            <p class="error-message"><?= htmlspecialchars($errorMessage) ?></p>
        <?php endif; ?>

        <table class="product-table">
            <thead>
                <tr>
                    <th><a href="?sort_by=name&order_direction=<?= ($orderDirection === 'ASC') ? 'DESC' : 'ASC' ?>">Название</a></th>
                    <th><a href="?sort_by=description&order_direction=<?= ($orderDirection === 'ASC') ? 'DESC' : 'ASC' ?>">Описание</a></th>
                    <th><a href="?sort_by=quantity&order_direction=<?= ($orderDirection === 'ASC') ? 'DESC' : 'ASC' ?>">Количество</a></th>
                    <th><a href="?sort_by=status&order_direction=<?= ($orderDirection === 'ASC') ? 'DESC' : 'ASC' ?>">Статус</a></th>
                    <th><a href="?sort_by=zone&order_direction=<?= ($orderDirection === 'ASC') ? 'DESC' : 'ASC' ?>">Зона</a></th>
                    <th>Действия</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($products) > 0): ?>
                    <?php foreach ($products as $product): ?>
                        <tr>
                            <td><?= isset($product['name']) ? htmlspecialchars($product['name']) : 'Не указано' ?></td>
                            <td><?= isset($product['description']) ? htmlspecialchars($product['description']) : 'Не указано' ?></td>
                            <td><?= isset($product['quantity']) ? htmlspecialchars($product['quantity']) : 'Не указано' ?></td>
                            <td><?= isset($product['status']) ? htmlspecialchars($product['status']) : 'Не указано' ?></td>
                            <td><?= isset($product['zone']) ? htmlspecialchars($product['zone']) : 'Не указано' ?></td>
                            <td>
                        
                                <button type="button" class="delete-button" onclick="confirmDelete(<?= $product['id'] ?>)">Удалить</button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6">Товары не найдены</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>

        <button onclick="redirectToPage(7)" class="add-button">Добавить товар</button>
    </section>
</main>

<script>
    function confirmDelete(productId) {
        const confirmation = confirm("Вы уверены, что хотите удалить этот товар?");
        if (confirmation) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = ''; 
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'delete_id';
            input.value = productId;
            form.appendChild(input);
            document.body.appendChild(form);
            form.submit(); 

     
            setTimeout(function() {
                location.reload();
            }, 500);
        }
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
            case 7: window.location.href = "add_product.php"; break;
            case 8: window.location.href = "excel.php"; break;
            case 9: window.location.href = "profile.php"; break;
            default: console.error("Неверный номер страницы: " + num); break;
        }
    }
</script>
</body>
</html>
