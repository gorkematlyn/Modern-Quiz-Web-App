<?php
define('PROJECT_ROOT', true); // Allow access to db.php
session_start();
require 'includes/db.php';

// Fetch categories from database
$categories = $conn->query("SELECT * FROM categories")->fetchAll(PDO::FETCH_ASSOC);

// Get selected category
$selectedCategorySlug = $_GET['category'] ?? null;
$selectedCategory = null;

if ($selectedCategorySlug) {
    // Find category name from slug
    foreach ($categories as $category) {
        $categorySlug = strtolower(str_replace(' ', '-', $category['name']));
        $categorySlug = preg_replace('/[^a-z0-9-]/', '', $categorySlug);
        if ($categorySlug === $selectedCategorySlug) {
            $selectedCategory = $category['name'];
            break;
        }
    }
}

// Fetch questions based on selected category
if ($selectedCategory) {
    $stmt = $conn->prepare("
        SELECT questions.*, categories.name AS category_name 
        FROM questions 
        LEFT JOIN categories ON questions.category_id = categories.id 
        WHERE categories.name = ?
        ORDER BY RAND() LIMIT 20
    ");
    $stmt->execute([$selectedCategory]);
} else {
    // If no category selected, fetch all questions
    $stmt = $conn->query("
        SELECT questions.*, categories.name AS category_name 
        FROM questions 
        LEFT JOIN categories ON questions.category_id = categories.id 
        ORDER BY RAND() LIMIT 20
    ");
}

$questions = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Create empty array for user answers if not exists in session
if (!isset($_SESSION['userAnswers'])) {
    $_SESSION['userAnswers'] = [];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quiz Page</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <style>
        body { background-color: #f8f9fa; }
        .container { max-width: 800px; margin: 50px auto; padding: 0 15px; }
        .card { margin-bottom: 20px; border: none; box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1); }
        .card-title { color: #ff6f00; font-weight: bold; }
        .form-check-label { color: #333; }
        .correct { color: green; font-weight: bold; }
        .incorrect { color: red; font-weight: bold; }
        .feedback { margin-top: 10px; font-size: 0.9em; }
        .btn-primary { background-color: #ff6f00; border: none; }
        .btn-primary:hover { background-color: #e65a00; }
        .timer { font-size: 1.5em; font-weight: bold; color: #ff6f00; text-align: center; margin-bottom: 20px; }
        .progress { margin-bottom: 20px; height: 25px; border-radius: 12px; }
        .progress-bar { background-color: #ff6f00; }
    </style>
</head>
<body>
    <div class="container">
        <h1 class="text-center mb-4" style="color: #ff6f00;">Quiz Page</h1>

        <!-- Category Selection -->
        <form method="GET" action="" class="mb-4" id="categoryForm">
            <div class="mb-3">
                <label for="category" class="form-label">Select Category</label>
                <select class="form-select" id="category" name="category" required>
                    <option value="">-- Select Category --</option>
                    <?php foreach ($categories as $category): ?>
                        <?php 
                            $categorySlug = strtolower(str_replace(' ', '-', $category['name']));
                            $categorySlug = preg_replace('/[^a-z0-9-]/', '', $categorySlug);
                        ?>
                        <option value="<?= $categorySlug ?>" <?= ($selectedCategory === $category['name']) ? 'selected' : '' ?>><?= $category['name'] ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <button type="submit" class="btn btn-primary" onclick="resetSession()">Start Quiz</button>
        </form>

        <?php if ($selectedCategory && !empty($questions)): ?>
            <div class="timer" id="timer">10:00</div>
            <div class="progress">
                <div class="progress-bar" id="progressBar" role="progressbar" style="width: 0%;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
            </div>
            <form id="testForm">
                <?php foreach ($questions as $index => $question): ?>
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">Question <?= $index + 1 ?>: <?= $question['question'] ?></h5>
                            <?php foreach (['option1', 'option2', 'option3', 'option4'] as $option): ?>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="question_<?= $question['id'] ?>" value="<?= $option ?>"
                                        <?= (isset($_SESSION['userAnswers'][$question['id']]) && $_SESSION['userAnswers'][$question['id']] === $option) ? 'checked' : '' ?>>
                                    <label class="form-check-label"><?= $question[$option] ?></label>
                                </div>
                            <?php endforeach; ?>
                            <div class="feedback mt-2" id="feedback_<?= $question['id'] ?>"></div>
                        </div>
                    </div>
                <?php endforeach; ?>
                <button type="submit" class="btn btn-primary btn-block">Show Results</button>
            </form>
        <?php elseif ($selectedCategory && empty($questions)): ?>
            <div class="alert alert-warning">There are no questions in the selected category yet.</div>
        <?php endif; ?>
    </div>

    <script>
        // Countdown function
        function startTimer(duration, display) {
            let timer = duration;
            const countdown = setInterval(function () {
                const minutes = parseInt(timer / 60, 10);
                const seconds = parseInt(timer % 60, 10);

                display.textContent = minutes.toString().padStart(2, '0') + ":" + seconds.toString().padStart(2, '0');

                if (--timer < 0) {
                    clearInterval(countdown);
                    document.getElementById('testForm').submit(); // Auto-submit form when time is up
                }
            }, 1000);
        }

        // Reset session
        function resetSession() {
            fetch('reset_session.php', {
                method: 'POST'
            });
        }

        // Check session when page loads
        window.onload = function () {
            // Clear session if a new category is selected
            if (window.location.search.includes('category=')) {
                resetSession();
            }

            const tenMinutes = 10 * 60; // 10 minutes
            const display = document.getElementById('timer');
            if (display) {
                startTimer(tenMinutes, display);
            }
        };

        // Update progress bar
        const totalQuestions = <?= count($questions) ?>;
        const progressBar = document.getElementById('progressBar');

        function updateProgress() {
            const answeredQuestions = document.querySelectorAll('input[type="radio"]:checked').length;
            const progress = (answeredQuestions / totalQuestions) * 100;
            progressBar.style.width = `${progress}%`;
            progressBar.setAttribute('aria-valuenow', progress);
        }

        // Listen to all radio buttons
        document.querySelectorAll('input[type="radio"]').forEach(radio => {
            radio.addEventListener('change', function () {
                updateProgress();

                const questionId = this.name.split('_')[1]; // Get question ID
                const selectedAnswer = this.value; // Get selected answer
                const feedbackDiv = document.getElementById(`feedback_${questionId}`);

                // Check correct answer with AJAX
                fetch('check_answer.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ questionId, selectedAnswer })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.correct) {
                        feedbackDiv.innerHTML = `<span class="correct">Correct Answer!</span>`;
                    } else {
                        feedbackDiv.innerHTML = `<span class="incorrect">Wrong Answer! <br> Explanation: ${data.explanation}</span>`;
                    }

                    // Save answer to session with AJAX
                    fetch('save_answer.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({ questionId, selectedAnswer })
                    });

                    // Disable all options for this question
                    const questionInputs = document.querySelectorAll(`input[name="question_${questionId}"]`);
                    questionInputs.forEach(input => {
                        input.disabled = true;
                    });
                });
            });
        });

        // Redirect to results page when form is submitted
        document.getElementById('testForm').addEventListener('submit', function (e) {
            e.preventDefault();
            
            // Check for unanswered questions
            const totalQuestions = <?= count($questions) ?>;
            const answeredQuestions = document.querySelectorAll('input[type="radio"]:checked').length;
            
            if (answeredQuestions < totalQuestions) {
                // Create modal HTML
                const modalHTML = `
                    <div class="modal fade" id="confirmModal" tabindex="-1" aria-labelledby="confirmModalLabel" aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="confirmModalLabel">Warning</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <p>You haven't answered all questions. Do you still want to continue?</p>
                                    <p>Unanswered Questions: ${totalQuestions - answeredQuestions}</p>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">No</button>
                                    <button type="button" class="btn btn-primary" id="confirmYes">Yes</button>
                                </div>
                            </div>
                        </div>
                    </div>
                `;

                // Remove old modal if exists
                const oldModal = document.getElementById('confirmModal');
                if (oldModal) {
                    oldModal.remove();
                }

                // Add new modal
                document.body.insertAdjacentHTML('beforeend', modalHTML);
                
                // Create modal object
                const modalElement = document.getElementById('confirmModal');
                const modal = new bootstrap.Modal(modalElement);
                
                // Add click event to Yes button
                modalElement.querySelector('#confirmYes').addEventListener('click', function() {
                    modal.hide();
                    window.location.href = 'result.php';
                });

                // Clean up when modal is closed
                modalElement.addEventListener('hidden.bs.modal', function() {
                    this.remove();
                });

                // Show modal
                modal.show();
            } else {
                // Redirect directly if all questions are answered
                window.location.href = 'result.php';
            }
        });

        // Update URL when form is submitted
        document.getElementById('categoryForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const category = document.getElementById('category').value;
            if (category) {
                // Clean and validate URL
                const cleanCategory = category
                    .toLowerCase()
                    .replace(/[^a-z0-9-]/g, '-') // Replace invalid characters with '-'
                    .replace(/-+/g, '-')         // Replace consecutive '-' with single '-'
                    .replace(/^-|-$/g, '');      // Remove '-' from beginning and end
                
                // Redirect to new URL
                window.location.href = `/?category=${cleanCategory}`;
            } else {
                window.location.href = '/';
            }
        });

        // Update progress bar when page loads
        updateProgress();
    </script>
</body>
</html>