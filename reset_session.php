<?php
session_start();
// Clear user answers from session
unset($_SESSION['userAnswers']);
echo json_encode(['status' => 'success']);
?> 