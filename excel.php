<?php
require_once 'config/db.php';  

$query = "SELECT id, name, description, quantity, status FROM products";
$stmt = $pdo->query($query);
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="products_report.csv"');

$output = fopen('php://output', 'w');

fwrite($output, "\xEF\xBB\xBF");

fputcsv($output, ['ID', 'Название', 'Описание', 'Количество', 'Статус']);

foreach ($products as $product) {
    fputcsv($output, [
        $product['id'],
        $product['name'], 
        $product['description'], 
        $product['quantity'],
        $product['status']
    ]);
}

fclose($output);
?>



<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Склад товаров</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <header class="header">
        <div class="pic">
            <span class="logo_text">Склад</span>
        </div>
        <nav class="nav_buttons">
            <button onclick="redirectToPage(1)" class="button">Войти</button>
            <button onclick="redirectToPage(2)" class="button">Регистрация</button>
            <button onclick="redirectToPage(7)" class="button">Отчёт</button>
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

            <form method="POST" action="">
                <button type="submit" name="generate_report" class="button">Сгенерировать отчет в Excel</button>
            </form>

            <table class="product-table">
                <thead>
                    <tr>
                        <th>Название</th>
                        <th>Описание</th>
                        <th>Количество</th>
                        <th>Статус</th>
                        <th>Зона</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $stmt = $pdo->prepare("SELECT * FROM products");
                    $stmt->execute();
                    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

                    foreach ($products as $product) {
                        echo "<tr>
                                <td>{$product['name']}</td>
                                <td>{$product['description']}</td>
                                <td>{$product['quantity']}</td>
                                <td>{$product['status']}</td>
                                <td>{$product['zone']}</td>
                            </tr>";
                    }
                    ?>
                </tbody>
            </table>
        </section>
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
                default: console.error("Неверный номер страницы: " + num); break;
            }
        }
    </script>
</body>
</html>
