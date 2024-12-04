<?php
require '../config.php';

if (!isset($_SESSION['admin'])) {
    header("Location: ../index.php");
    exit;
}

$book_id = $_GET['id'] ?? null;

if (!$book_id) {
    header("Location: list_books.php");
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM books WHERE id = ? AND admin_id = ?");
$stmt->execute([$book_id, $_SESSION['admin']]);
$book = $stmt->fetch();

if (!$book) {
    header("Location: list_books.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'];
    $author = $_POST['author'];
    $publisher = $_POST['publisher'];
    $year_published = $_POST['year_published'];
    $category = $_POST['category'];
    $available = isset($_POST['available']) ? 1 : 0;

    $update = $pdo->prepare("UPDATE books SET title = ?, author = ?, publisher = ?, year_published = ?, category = ?, available = ? WHERE id = ? AND admin_id = ?");
    $update->execute([$title, $author, $publisher, $year_published, $category, $available, $book_id, $_SESSION['admin']]);
    header("Location: list_books.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Book</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/styles.css">
</head>
<body>
    <div class="container mt-4">
        <h1>Edit Book</h1>
        <form method="POST">
            <div class="mb-3">
                <label for="title" class="form-label">Title</label>
                <input type="text" class="form-control" id="title" name="title" value="<?= htmlspecialchars($book['title']) ?>" required>
            </div>
            <div class="mb-3">
                <label for="author" class="form-label">Author</label>
                <input type="text" class="form-control" id="author" name="author" value="<?= htmlspecialchars($book['author']) ?>" required>
            </div>
            <div class="mb-3">
                <label for="publisher" class="form-label">Publisher</label>
                <input type="text" class="form-control" id="publisher" name="publisher" value="<?= htmlspecialchars($book['publisher']) ?>" required>
            </div>
            <div class="mb-3">
                <label for="year_published" class="form-label">Year Published</label>
                <input type="number" class="form-control" id="year_published" name="year_published" value="<?= htmlspecialchars($book['year_published']) ?>" required>
            </div>
            <div class="mb-3">
                <label for="category" class="form-label">Category</label>
                <input type="text" class="form-control" id="category" name="category" value="<?= htmlspecialchars($book['category']) ?>" required>
            </div>
            <div class="mb-3 form-check">
                <input type="checkbox" class="form-check-input" id="available" name="available" <?= $book['available'] ? 'checked' : '' ?>>
                <label for="available" class="form-check-label">Available</label>
            </div>
            <button type="submit" class="btn btn-green">Update</button>
            <a href="list_books.php" class="btn btn-secondary">Cancel</a>
        </form>
    </div>
</body>
</html>
