<?php
require '../config.php';

if (!isset($_SESSION['admin'])) {
    header("Location: ../index.php");
    exit;
}

$admin_id = $_SESSION['admin']; 

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'];
    $author = $_POST['author'];
    $publisher = $_POST['publisher'];
    $year_published = $_POST['year_published'];
    $category = $_POST['category'];
    $available = isset($_POST['available']) ? 1 : 0;

    $stmt = $pdo->prepare("INSERT INTO books (title, author, publisher, year_published, category, available, admin_id) 
                           VALUES (?, ?, ?, ?, ?, ?, ?)");
    try {
        $stmt->execute([$title, $author, $publisher, $year_published, $category, $available, $admin_id]);
        $success = "Book added successfully!";
    } catch (Exception $e) {
        $error = "Failed to add book. Please try again.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Book</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/styles.css">
</head>
<body>
    <div class="container mt-4">
        <h1 class="mb-4">Add a New Book</h1>

        <?php if (!empty($success)): ?>
            <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
        <?php elseif (!empty($error)): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="mb-3">
                <label for="title" class="form-label">Title</label>
                <input type="text" name="title" id="title" class="form-control" required>
            </div>
            <div class="mb-3">
                <label for="author" class="form-label">Author</label>
                <input type="text" name="author" id="author" class="form-control" required>
            </div>
            <div class="mb-3">
                <label for="publisher" class="form-label">Publisher</label>
                <input type="text" name="publisher" id="publisher" class="form-control">
            </div>
            <div class="mb-3">
                <label for="year_published" class="form-label">Year Published</label>
                <input type="number" name="year_published" id="year_published" class="form-control" min="1000" max="<?= date('Y') ?>">
            </div>
            <div class="mb-3">
                <label for="category" class="form-label">Category</label>
                <input type="text" name="category" id="category" class="form-control">
            </div>
            <div class="form-check mb-3">
                <input type="checkbox" name="available" id="available" class="form-check-input">
                <label for="available" class="form-check-label">Available</label>
            </div>
            <button type="submit" class="btn btn-green">Add Book</button>
            <a href="list_books.php" class="btn btn-secondary">Back to Book List</a>
        </form>
    </div>
</body>
</html>
