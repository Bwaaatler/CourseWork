<?php
session_start();
require_once 'config/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$errorMessage = "";
$successMessage = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_order'])) {
    $productIds = isset($_POST['product_ids']) ? $_POST['product_ids'] : [];
    $quantities = isset($_POST['quantities']) ? $_POST['quantities'] : [];

    if (empty($productIds) || empty($quantities)) {
        $errorMessage = "Выберите товары и укажите количество!";
    } else {
        try {
    
            foreach ($productIds as $index => $productId) {
                $quantity = $quantities[$index];

           
                $stmt = $pdo->prepare("SELECT quantity FROM products WHERE id = ?");
                $stmt->execute([$productId]);
                $product = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($product['quantity'] >= $quantity) {
          
                    $stmt = $pdo->prepare("INSERT INTO dispatch (product_id, quantity, status) VALUES (?, ?, 'Ожидает')");
                    $stmt->execute([$productId, $quantity]);
                } else {
                    $errorMessage = "Недостаточно товара на складе для продукта с ID: $productId";
                    break;
                }
            }

            if (empty($errorMessage)) {
                $successMessage = "Заказ успешно добавлен!";
            }
        } catch (PDOException $e) {
            $errorMessage = "Ошибка при добавлении заказа: " . $e->getMessage();
        }
    }
}


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['send_order'])) {
    $orderId = $_POST['order_id'];
    $dispatchDate = $_POST['dispatch_date'];

    try {
        $stmt = $pdo->prepare("UPDATE dispatch SET status = 'Отправлено', dispatch_date = ? WHERE id = ?");
        $stmt->execute([$dispatchDate, $orderId]);

        $stmt = $pdo->prepare("SELECT product_id, quantity FROM dispatch WHERE id = ?");
        $stmt->execute([$orderId]);
        $orderItems = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($orderItems as $item) {
            $stmt = $pdo->prepare("UPDATE products SET quantity = quantity - ? WHERE id = ?");
            $stmt->execute([$item['quantity'], $item['product_id']]);
        }

        $successMessage = "Заказ успешно отправлен!";
    } catch (PDOException $e) {
        $errorMessage = "Ошибка при отправке заказа: " . $e->getMessage();
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['clear_order'])) {
    $orderId = $_POST['order_id'];

    try {
        $stmt = $pdo->prepare("DELETE FROM dispatch WHERE id = ?");
        $stmt->execute([$orderId]);

        $successMessage = "Заказ успешно удалён!";
    } catch (PDOException $e) {
        $errorMessage = "Ошибка при удалении заказа: " . $e->getMessage();
    }
}

$stmt = $pdo->query("SELECT * FROM dispatch");
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

$stmt = $pdo->query("SELECT id, name FROM products");
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Заказы</title>
    <link rel="stylesheet" href="assets/css/style.css?v=1.0">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #FFFDE7;
            margin: 0;
            padding: 0;
        }


        .header .logo_text {
            font-size: 24px;
            font-weight: bold;
        }

        .main_window {
            padding: 20px;
        }

        h1, h2 {
            color: #333;
        }

        .form-container {
            background-color: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
        }

        .form-container select, .form-container input, .form-container button {
            width: 100%;
            padding: 10px;
            margin-bottom: 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }

        .form-container button {
            background-color: #28a745;
            color: white;
            font-size: 16px;
            border: none;
            cursor: pointer;
        }

        .form-container button:hover {
            background-color: #218838;
        }

        .error-message, .success-message {
            color: #f44336;
            font-weight: bold;
        }

        .table-container {
            background-color: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        th, td {
            padding: 12px;
            text-align: center;
            border: 1px solid #ddd;
        }

        th {
            background-color: #007BFF;
            color: white;
        }

        td {
            background-color: #FFF8E1;
        }

        td button {
            background-color: #ff5722;
            color: white;
            border: none;
            padding: 5px 10px;
            border-radius: 4px;
            cursor: pointer;
        }

        td button:hover {
            background-color: #e64a19;
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
        <button onclick="redirectToPage(7)" class="button">Отчёт</button>
        <button onclick="redirectToPage(9)" class="button">Личный кабинет</button>
    </nav>
</header>

<main class="main_window">
<div class="navigation">
            <button  onclick="redirectToPage(3)" class="nav-button">Информация</button>
            <button  onclick="redirectToPage(4)" class="nav-button">Пользователи</button>
            <button  onclick="redirectToPage(5)" class="nav-button">На вывоз</button>
            <button  onclick="redirectToPage(6)" class="nav-button">О сайте</button>
        </div>
    <h1>Управление заказами</h1>

    <?php if (!empty($errorMessage)): ?>
        <p class="error-message"><?php echo htmlspecialchars($errorMessage); ?></p>
    <?php endif; ?>
    
    <?php if (!empty($successMessage)): ?>
        <p class="success-message"><?php echo htmlspecialchars($successMessage); ?></p>
    <?php endif; ?>

    <div class="form-container">
        <h2>Добавить заказ</h2>
        <form method="POST" action="">
            <label for="product_ids">Выберите товары:</label>
            <select name="product_ids[]" multiple required>
                <?php foreach ($products as $product): ?>
                    <option value="<?= $product['id'] ?>"><?= htmlspecialchars($product['name']) ?></option>
                <?php endforeach; ?>
            </select>

            <label for="quantities">Количество товаров:</label>
            <input type="number" name="quantities[]" required>

            <button type="submit" name="add_order">Добавить заказ</button>
        </form>
    </div>

    <div class="table-container">
        <h2>Все заказы</h2>
        <table>
            <thead>
                <tr>
                    <th>Товары</th>
                    <th>Количество</th>
                    <th>Статус</th>
                    <th>Действия</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($orders as $order): ?>
                    <tr>
                        <td>
                            <?php
                    
                            $stmt = $pdo->prepare("SELECT name FROM products WHERE id IN (SELECT product_id FROM dispatch WHERE id = ?)");
                            $stmt->execute([$order['id']]);
                            $orderProducts = $stmt->fetchAll(PDO::FETCH_ASSOC);
                            foreach ($orderProducts as $product) {
                                echo htmlspecialchars($product['name']) . "<br>";
                            }
                            ?>
                        </td>
                        <td><?= htmlspecialchars($order['quantity']) ?></td>
                        <td><?= htmlspecialchars($order['status']) ?></td>
                        <td>
                            <?php if ($order['status'] == 'Ожидает'): ?>
                                <form method="POST" action="">
                                    <input type="hidden" name="order_id" value="<?= $order['id'] ?>">
                                    <input type="date" name="dispatch_date" required>
                                    <button type="submit" name="send_order">Отправить</button>
                                </form>
                            <?php endif; ?>

                            <?php if ($order['status'] == 'Отправлено'): ?>
                                <form method="POST" action="">
                                    <input type="hidden" name="order_id" value="<?= $order['id'] ?>">
                                    <button type="submit" name="clear_order">Очистить</button>
                                </form>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
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