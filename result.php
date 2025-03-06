<?php
define('PROJECT_ROOT', true); // Allow access to db.php
session_start();
require 'includes/db.php';

// Check if user has answers
if (!isset($_SESSION['userAnswers']) || empty($_SESSION['userAnswers'])) {
    header('Location: index.php');
    exit;
}

// Get questions and calculate score
$questionIds = array_keys($_SESSION['userAnswers']);
$placeholders = str_repeat('?,', count($questionIds) - 1) . '?';

$stmt = $conn->prepare("
    SELECT questions.*, categories.name AS category_name 
    FROM questions 
    LEFT JOIN categories ON questions.category_id = categories.id 
    WHERE questions.id IN ($placeholders)
");
$stmt->execute($questionIds);
$questions = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Calculate score
$totalQuestions = count($questions);
$correctAnswers = 0;

foreach ($questions as $question) {
    if ($_SESSION['userAnswers'][$question['id']] === $question['correct_answer']) {
        $correctAnswers++;
    }
}

$score = ($correctAnswers / $totalQuestions) * 100;

// Clear session after showing results
unset($_SESSION['userAnswers']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quiz Results</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #f8f9fa; }
        .container { max-width: 800px; margin: 50px auto; padding: 0 15px; }
        .card { margin-bottom: 20px; border: none; box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1); }
        .card-title { color: #ff6f00; font-weight: bold; }
        .btn-primary { background-color: #ff6f00; border: none; }
        .btn-primary:hover { background-color: #e65a00; }
        .score-circle {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            background-color: #ff6f00;
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2em;
            margin: 20px auto;
        }
        .correct { color: green; }
        .incorrect { color: red; }
    </style>
</head>
<body>
    <div class="container">
        <h1 class="text-center mb-4" style="color: #ff6f00;">Quiz Results</h1>

        <!-- Score Display -->
        <div class="card mb-4">
            <div class="card-body text-center">
                <div class="score-circle">
                    <?= round($score) ?>%
                </div>
                <h3>Your Score</h3>
                <p>Correct Answers: <?= $correctAnswers ?> out of <?= $totalQuestions ?></p>
            </div>
        </div>

        <!-- Detailed Results -->
        <div class="card">
            <div class="card-header">
                <h3 class="mb-0">Detailed Results</h3>
            </div>
            <div class="card-body">
                <?php foreach ($questions as $index => $question): ?>
                    <div class="mb-4">
                        <h5>Question <?= $index + 1 ?>: <?= $question['question'] ?></h5>
                        <p class="mb-2">Your Answer: 
                            <span class="<?= ($_SESSION['userAnswers'][$question['id']] === $question['correct_answer']) ? 'correct' : 'incorrect' ?>">
                                <?= $question['option' . $_SESSION['userAnswers'][$question['id']]] ?>
                            </span>
                        </p>
                        <?php if ($_SESSION['userAnswers'][$question['id']] !== $question['correct_answer']): ?>
                            <p class="mb-2">Correct Answer: 
                                <span class="correct"><?= $question['option' . $question['correct_answer']] ?></span>
                            </p>
                            <p class="mb-0"><small>Explanation: <?= $question['explanation'] ?></small></p>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="text-center mt-4">
            <a href="index.php" class="btn btn-primary">Try Another Quiz</a>
        </div>
    </div>
</body>
</html>