// Global variables
let currentCategory = null;
let userAnswers = {};
let timer = null;

// DOM elements
const categoryForm = document.getElementById('categoryForm');
const quizContainer = document.getElementById('quizContainer');
const questionsContainer = document.getElementById('questionsContainer');
const progressBar = document.getElementById('progressBar');
const timerDisplay = document.getElementById('timer');

// Start timer function
function startTimer(duration, display) {
    let timer = duration;
    const countdown = setInterval(function () {
        const minutes = parseInt(timer / 60, 10);
        const seconds = parseInt(timer % 60, 10);

        display.textContent = minutes.toString().padStart(2, '0') + ":" + seconds.toString().padStart(2, '0');

        if (--timer < 0) {
            clearInterval(countdown);
            showResults(); // Auto-submit when time is up
        }
    }, 1000);
    return countdown;
}

// Update progress bar
function updateProgress() {
    const totalQuestions = questions[currentCategory].length;
    const answeredQuestions = Object.keys(userAnswers).length;
    const progress = (answeredQuestions / totalQuestions) * 100;
    progressBar.style.width = `${progress}%`;
    progressBar.setAttribute('aria-valuenow', progress);
}

// Load questions for selected category
function loadQuestions(category) {
    currentCategory = category;
    userAnswers = {};
    
    const categoryQuestions = questions[category];
    let html = '';

    categoryQuestions.forEach((q, index) => {
        html += `
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Question ${index + 1}: ${q.question}</h5>
                    ${q.options.map((option, i) => `
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="question_${q.id}" value="${i}" 
                                onchange="checkAnswer(${q.id}, ${i})">
                            <label class="form-check-label">${option}</label>
                        </div>
                    `).join('')}
                    <div class="feedback mt-2" id="feedback_${q.id}"></div>
                </div>
            </div>
        `;
    });

    questionsContainer.innerHTML = html;
    quizContainer.style.display = 'block';
    updateProgress();

    // Start timer (10 minutes)
    if (timer) clearInterval(timer);
    timer = startTimer(10 * 60, timerDisplay);
}

// Check answer
function checkAnswer(questionId, selectedAnswer) {
    const question = questions[currentCategory].find(q => q.id === questionId);
    const feedbackDiv = document.getElementById(`feedback_${questionId}`);
    const isCorrect = selectedAnswer === question.correctAnswer;

    userAnswers[questionId] = selectedAnswer;

    // Show feedback
    if (isCorrect) {
        feedbackDiv.innerHTML = `<span class="correct">Correct Answer!</span>`;
    } else {
        feedbackDiv.innerHTML = `<span class="incorrect">Wrong Answer! <br> Explanation: ${question.explanation}</span>`;
    }

    // Disable all options for this question
    const questionInputs = document.querySelectorAll(`input[name="question_${questionId}"]`);
    questionInputs.forEach(input => {
        input.disabled = true;
    });

    updateProgress();
}

// Show results
function showResults() {
    const categoryQuestions = questions[currentCategory];
    let correctAnswers = 0;

    categoryQuestions.forEach(q => {
        if (userAnswers[q.id] === q.correctAnswer) {
            correctAnswers++;
        }
    });

    const score = Math.round((correctAnswers / categoryQuestions.length) * 100);
    
    // Create modal HTML
    const modalHTML = `
        <div class="modal fade" id="resultsModal" tabindex="-1" aria-labelledby="resultsModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="resultsModalLabel">Quiz Results</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <h4>Your Score: ${score}%</h4>
                        <p>Correct Answers: ${correctAnswers} out of ${categoryQuestions.length}</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="button" class="btn btn-primary" onclick="location.reload()">Try Again</button>
                    </div>
                </div>
            </div>
        </div>
    `;

    // Remove old modal if exists
    const oldModal = document.getElementById('resultsModal');
    if (oldModal) {
        oldModal.remove();
    }

    // Add new modal
    document.body.insertAdjacentHTML('beforeend', modalHTML);
    
    // Show modal
    const modal = new bootstrap.Modal(document.getElementById('resultsModal'));
    modal.show();

    // Clear timer
    if (timer) clearInterval(timer);
}

// Event listeners
categoryForm.addEventListener('submit', function(e) {
    e.preventDefault();
    const category = document.getElementById('category').value;
    if (category) {
        loadQuestions(category);
    }
});

document.getElementById('testForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const categoryQuestions = questions[currentCategory];
    const answeredQuestions = Object.keys(userAnswers).length;
    
    if (answeredQuestions < categoryQuestions.length) {
        // Create warning modal
        const modalHTML = `
            <div class="modal fade" id="warningModal" tabindex="-1" aria-labelledby="warningModalLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="warningModalLabel">Warning</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <p>You haven't answered all questions. Do you still want to continue?</p>
                            <p>Unanswered Questions: ${categoryQuestions.length - answeredQuestions}</p>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">No</button>
                            <button type="button" class="btn btn-primary" onclick="showResults()">Yes</button>
                        </div>
                    </div>
                </div>
            </div>
        `;

        // Remove old modal if exists
        const oldModal = document.getElementById('warningModal');
        if (oldModal) {
            oldModal.remove();
        }

        // Add new modal
        document.body.insertAdjacentHTML('beforeend', modalHTML);
        
        // Show modal
        const modal = new bootstrap.Modal(document.getElementById('warningModal'));
        modal.show();
    } else {
        showResults();
    }
}); 