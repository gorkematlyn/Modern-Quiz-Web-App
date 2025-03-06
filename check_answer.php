<?php
define('PROJECT_ROOT', true); // Allow access to db.php
// Include database connection
require 'includes/db.php';

// Get JSON data
$data = json_decode(file_get_contents('php://input'), true);
$questionId = $data['questionId'];
$selectedAnswer = $data['selectedAnswer'];

// Fetch question from database
$stmt = $conn->prepare("SELECT * FROM questions WHERE id = ?");
$stmt->execute([$questionId]);
$question = $stmt->fetch(PDO::FETCH_ASSOC);

// Check answer
$correct = ($selectedAnswer === $question['correct_answer']);

// Return response as JSON
echo json_encode([
    'correct' => $correct,
    'correctAnswer' => $question['correct_answer'],
    'explanation' => $question['explanation']
]);
?>