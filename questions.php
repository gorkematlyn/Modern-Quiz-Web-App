<?php
define('PROJECT_ROOT', true); // Allow access to db.php
session_start();
require 'includes/db.php';

// Fetch questions and category names from database
$stmt = $conn->query("
    SELECT questions.*, categories.name AS category_name 
    FROM questions 
    LEFT JOIN categories ON questions.category_id = categories.id
");
$questions = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Delete question operation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_question'])) {
    $questionId = $_POST['question_id'];

    try {
        $stmt = $conn->prepare("DELETE FROM questions WHERE id = ?");
        $stmt->execute([$questionId]);
        echo "<div class='alert alert-success'>Question deleted successfully!</div>";
        // Refresh page
        header("Refresh:0");
    } catch (PDOException $e) {
        echo "<div class='alert alert-danger'>Error deleting question: " . $e->getMessage() . "</div>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Questions</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #f8f9fa; }
        .container { max-width: 800px; margin: 50px auto; padding: 0 15px; }
        .card { margin-bottom: 20px; border: none; box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1); }
        .card-title { color: #ff6f00; font-weight: bold; }
        .btn-primary { background-color: #ff6f00; border: none; }
        .btn-primary:hover { background-color: #e65a00; }
    </style>
</head>
<body>
    <div class="container">
        <h1 class="text-center mb-4" style="color: #ff6f00;">Manage Questions</h1>

        <!-- Questions List -->
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Question</th>
                        <th>Category</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($questions as $question): ?>
                        <tr>
                            <td><?= $question['question'] ?></td>
                            <td><?= $question['category_name'] ?></td>
                            <td>
                                <a href="edit_question.php?id=<?= $question['id'] ?>" class="btn btn-sm btn-primary">Edit</a>
                                <form method="POST" style="display: inline;">
                                    <input type="hidden" name="question_id" value="<?= $question['id'] ?>">
                                    <button type="submit" name="delete_question" class="btn btn-sm btn-danger" 
                                            onclick="return confirm('Are you sure you want to delete this question?')">Delete</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <div class="text-center mt-4">
            <a href="edit_question.php" class="btn btn-primary">Add New Question</a>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>