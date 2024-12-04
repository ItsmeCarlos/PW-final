<?php
require '../config.php';

if (!isset($_SESSION['admin'])) {
    header("Location: ../index.php");
    exit;
}

$admin_id = $_SESSION['admin'];
$search = $_GET['search'] ?? '';
$order_by = $_GET['order_by'] ?? 'title';
$sort_direction = $_GET['sort_direction'] ?? 'ASC';

$valid_columns = ['title', 'author', 'publisher', 'year_published', 'category', 'available'];
$valid_directions = ['ASC', 'DESC'];

if (!in_array($order_by, $valid_columns)) {
    $order_by = 'title';
}

if (!in_array($sort_direction, $valid_directions)) {
    $sort_direction = 'ASC';
}

$stmt = $pdo->prepare("SELECT * FROM books WHERE admin_id = ? AND (title LIKE ? OR author LIKE ?) ORDER BY $order_by $sort_direction");
$stmt->execute([$admin_id, "%$search%", "%$search%"]);
$books = $stmt->fetchAll();

if (isset($_POST['delete_id'])) {
    $delete_id = $_POST['delete_id'];
    $stmt = $pdo->prepare("DELETE FROM books WHERE id = ? AND admin_id = ?");
    $stmt->execute([$delete_id, $admin_id]);
    header("Location: list_books.php");
    exit;
}

$year_data = $pdo->prepare("SELECT year_published, COUNT(*) as count FROM books WHERE admin_id = ? GROUP BY year_published ORDER BY year_published");
$year_data->execute([$admin_id]);
$year_stats = $year_data->fetchAll();

$category_data = $pdo->prepare("SELECT category, COUNT(*) as count FROM books WHERE admin_id = ? GROUP BY category");
$category_data->execute([$admin_id]);
$category_stats = $category_data->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book List</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/styles.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <div class="container mt-5">
        <div class="d-flex justify-content-between align-items-center">
            <h1 class="text-success">Your Book List</h1>
            <a href="logout.php" class="btn btn-danger btn-sm">Logout</a>
        </div>
        <form method="GET" class="mt-3 mb-3">
            <div class="input-group">
                <input type="text" name="search" class="form-control" placeholder="Search by title or author" value="<?= htmlspecialchars($search) ?>">
                <button type="submit" class="btn btn-outline-success">Search</button>
            </div>
        </form>
        <a href="add_book.php" class="btn btn-success mb-3">Add Book</a>
        <table class="table table-hover table-bordered">
            <thead class="table-success">
                <tr>
                    <th><a href="?order_by=title&sort_direction=<?= ($order_by == 'title' && $sort_direction == 'ASC') ? 'DESC' : 'ASC' ?>">Title <?= ($order_by == 'title') ? ($sort_direction == 'ASC' ? '▲' : '▼') : '' ?></a></th>
                    <th><a href="?order_by=author&sort_direction=<?= ($order_by == 'author' && $sort_direction == 'ASC') ? 'DESC' : 'ASC' ?>">Author <?= ($order_by == 'author') ? ($sort_direction == 'ASC' ? '▲' : '▼') : '' ?></a></th>
                    <th><a href="?order_by=publisher&sort_direction=<?= ($order_by == 'publisher' && $sort_direction == 'ASC') ? 'DESC' : 'ASC' ?>">Publisher <?= ($order_by == 'publisher') ? ($sort_direction == 'ASC' ? '▲' : '▼') : '' ?></a></th>
                    <th><a href="?order_by=year_published&sort_direction=<?= ($order_by == 'year_published' && $sort_direction == 'ASC') ? 'DESC' : 'ASC' ?>">Year Published <?= ($order_by == 'year_published') ? ($sort_direction == 'ASC' ? '▲' : '▼') : '' ?></a></th>
                    <th><a href="?order_by=category&sort_direction=<?= ($order_by == 'category' && $sort_direction == 'ASC') ? 'DESC' : 'ASC' ?>">Category <?= ($order_by == 'category') ? ($sort_direction == 'ASC' ? '▲' : '▼') : '' ?></a></th>
                    <th><a href="?order_by=available&sort_direction=<?= ($order_by == 'available' && $sort_direction == 'ASC') ? 'DESC' : 'ASC' ?>">Available <?= ($order_by == 'available') ? ($sort_direction == 'ASC' ? '▲' : '▼') : '' ?></a></th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($books as $book): ?>
                    <tr>
                        <td><?= htmlspecialchars($book['title']) ?></td>
                        <td><?= htmlspecialchars($book['author']) ?></td>
                        <td><?= htmlspecialchars($book['publisher']) ?></td>
                        <td><?= htmlspecialchars($book['year_published']) ?></td>
                        <td><?= htmlspecialchars($book['category']) ?></td>
                        <td><?= $book['available'] ? 'Yes' : 'No' ?></td>
                        <td class="d-flex justify-content-center">
                            <a href="edit_book.php?id=<?= $book['id'] ?>" class="btn btn-warning btn-sm mx-1">Edit</a>
                            <form method="POST" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete this book?');">
                                <input type="hidden" name="delete_id" value="<?= $book['id'] ?>">
                                <button type="submit" class="btn btn-danger btn-sm mx-1">Remove</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <h3 class="mt-5">Statistics</h3>
        <div class="row mt-4">
            <div class="col-md-6">
                <h4>Books by Year</h4>
                <canvas id="yearChart"></canvas>
            </div>
            <div class="col-md-6">
                <h4>Books by Category</h4>
                <canvas id="categoryChart"></canvas>
            </div>
        </div>
    </div>

    <script>
        const yearCtx = document.getElementById('yearChart').getContext('2d');
        const yearChart = new Chart(yearCtx, {
            type: 'bar',
            data: {
                labels: <?= json_encode(array_column($year_stats, 'year_published')) ?>,
                datasets: [{
                    label: 'Books Published',
                    data: <?= json_encode(array_column($year_stats, 'count')) ?>,
                    backgroundColor: 'rgba(76, 175, 80, 0.5)',
                    borderColor: 'rgba(76, 175, 80, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });

        const categoryCtx = document.getElementById('categoryChart').getContext('2d');
        const categoryChart = new Chart(categoryCtx, {
            type: 'pie',
            data: {
                labels: <?= json_encode(array_column($category_stats, 'category')) ?>,
                datasets: [{
                    label: 'Books by Category',
                    data: <?= json_encode(array_column($category_stats, 'count')) ?>,
                    backgroundColor: [
                        'rgba(76, 175, 80, 0.5)',
                        'rgba(255, 99, 132, 0.5)',
                        'rgba(54, 162, 235, 0.5)',
                        'rgba(255, 206, 86, 0.5)',
                        'rgba(75, 192, 192, 0.5)',
                        'rgba(153, 102, 255, 0.5)'
                    ],
                    borderColor: [
                        'rgba(76, 175, 80, 1)',
                        'rgba(255, 99, 132, 1)',
                        'rgba(54, 162, 235, 1)',
                        'rgba(255, 206, 86, 1)',
                        'rgba(75, 192, 192, 1)',
                        'rgba(153, 102, 255, 1)'
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true
            }
        });
    </script>
</body>
</html>
