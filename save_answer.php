<?php
define('PROJECT_ROOT', true); // Allow access to db.php
session_start();

// Get JSON data
$data = json_decode(file_get_contents('php://input'), true);
$questionId = $data['questionId'];
$selectedAnswer = $data['selectedAnswer'];

// Save answer to session
$_SESSION['userAnswers'][$questionId] = $selectedAnswer;

echo json_encode(['status' => 'success']);
?>