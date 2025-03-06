<?php
define('PROJECT_ROOT', true); // Allow access to db.php
session_start();
require 'includes/db.php';

// Get categories for dropdown
$categories = $conn->query("SELECT * FROM categories")->fetchAll(PDO::FETCH_ASSOC);

// Initialize variables
$question = [
    'id' => '',
    'question' => '',
    'option1' => '',
    'option2' => '',
    'option3' => '',
    'option4' => '',
    'correct_answer' => '',
    'explanation' => '',
    'category_id' => ''
];

// If editing existing question
if (isset($_GET['id'])) {
    $stmt = $conn->prepare("SELECT * FROM questions WHERE id = ?");
    $stmt->execute([$_GET['id']]);
    $question = $stmt->fetch(PDO::FETCH_ASSOC);
}

// Form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $isNewQuestion = empty($_POST['id']);
    
    try {
        if ($isNewQuestion) {
            // Insert new question
            $stmt = $conn->prepare("
                INSERT INTO questions (question, option1, option2, option3, option4, correct_answer, explanation, category_id)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)
            ");
        } else {
            // Update existing question
            $stmt = $conn->prepare("
                UPDATE questions 
                SET question = ?, option1 = ?, option2 = ?, option3 = ?, option4 = ?, 
                    correct_answer = ?, explanation = ?, category_id = ?
                WHERE id = ?
            ");
        }

        $params = [
            $_POST['question'],
            $_POST['option1'],
            $_POST['option2'],
            $_POST['option3'],
            $_POST['option4'],
            $_POST['correct_answer'],
            $_POST['explanation'],
            $_POST['category_id']
        ];

        if (!$isNewQuestion) {
            $params[] = $_POST['id'];
        }

        $stmt->execute($params);
        
        $message = $isNewQuestion ? "Question added successfully!" : "Question updated successfully!";
        echo "<div class='alert alert-success'>$message</div>";
        
        // Redirect to questions list after a short delay
        header("refresh:1;url=questions.php");
        exit;
    } catch (PDOException $e) {
        echo "<div class='alert alert-danger'>Error: " . $e->getMessage() . "</div>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($_GET['id']) ? 'Edit' : 'Add' ?> Question</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #f8f9fa; }
        .container { max-width: 800px; margin: 50px auto; padding: 0 15px; }
        .card { border: none; box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1); }
        .btn-primary { background-color: #ff6f00; border: none; }
        .btn-primary:hover { background-color: #e65a00; }
    </style>
</head>
<body>
    <div class="container">
        <div class="card">
            <div class="card-header">
                <h2 class="text-center" style="color: #ff6f00;">
                    <?= isset($_GET['id']) ? 'Edit' : 'Add' ?> Question
                </h2>
            </div>
            <div class="card-body">
                <form method="POST">
                    <input type="hidden" name="id" value="<?= $question['id'] ?>">
                    
                    <div class="mb-3">
                        <label class="form-label">Category</label>
                        <select name="category_id" class="form-select" required>
                            <option value="">Select Category</option>
                            <?php foreach ($categories as $category): ?>
                                <option value="<?= $category['id'] ?>" <?= ($category['id'] == $question['category_id']) ? 'selected' : '' ?>>
                                    <?= $category['name'] ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Question</label>
                        <input type="text" name="question" class="form-control" value="<?= $question['question'] ?>" required>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Option 1</label>
                            <input type="text" name="option1" class="form-control" value="<?= $question['option1'] ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Option 2</label>
                            <input type="text" name="option2" class="form-control" value="<?= $question['option2'] ?>" required>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Option 3</label>
                            <input type="text" name="option3" class="form-control" value="<?= $question['option3'] ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Option 4</label>
                            <input type="text" name="option4" class="form-control" value="<?= $question['option4'] ?>" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Correct Answer</label>
                        <select name="correct_answer" class="form-select" required>
                            <option value="">Select Correct Answer</option>
                            <option value="1" <?= ($question['correct_answer'] == 1) ? 'selected' : '' ?>>Option 1</option>
                            <option value="2" <?= ($question['correct_answer'] == 2) ? 'selected' : '' ?>>Option 2</option>
                            <option value="3" <?= ($question['correct_answer'] == 3) ? 'selected' : '' ?>>Option 3</option>
                            <option value="4" <?= ($question['correct_answer'] == 4) ? 'selected' : '' ?>>Option 4</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Explanation</label>
                        <textarea name="explanation" class="form-control" rows="3" required><?= $question['explanation'] ?></textarea>
                    </div>

                    <div class="text-center">
                        <button type="submit" class="btn btn-primary"><?= isset($_GET['id']) ? 'Update' : 'Add' ?> Question</button>
                        <a href="questions.php" class="btn btn-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>