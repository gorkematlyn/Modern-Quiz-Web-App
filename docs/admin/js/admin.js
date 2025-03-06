// Initialize modals
const addQuestionModal = new bootstrap.Modal(document.getElementById('addQuestionModal'));
const addCategoryModal = new bootstrap.Modal(document.getElementById('addCategoryModal'));
const importModal = new bootstrap.Modal(document.getElementById('importModal'));

// Load questions and categories when page loads
document.addEventListener('DOMContentLoaded', function() {
    loadQuestions();
    loadCategories();
});

// Load questions
function loadQuestions() {
    const questionsList = document.getElementById('questionsList');
    let html = '';

    Object.entries(questions).forEach(([category, categoryQuestions]) => {
        html += `
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">${getCategoryName(category)}</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Question</th>
                                    <th>Options</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
        `;

        categoryQuestions.forEach(q => {
            html += `
                <tr>
                    <td>${q.question}</td>
                    <td>
                        <small>
                            ${q.options.map((opt, i) => 
                                `${i + 1}. ${opt}${i === q.correctAnswer ? ' ✓' : ''}`
                            ).join('<br>')}
                        </small>
                    </td>
                    <td class="action-buttons">
                        <button class="btn btn-sm btn-outline-primary" onclick="editQuestion(${q.id})">
                            <i class="bi bi-pencil"></i>
                        </button>
                        <button class="btn btn-sm btn-outline-danger" onclick="deleteQuestion(${q.id})">
                            <i class="bi bi-trash"></i>
                        </button>
                    </td>
                </tr>
            `;
        });

        html += `
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        `;
    });

    questionsList.innerHTML = html;
}

// Load categories
function loadCategories() {
    const categoriesList = document.getElementById('categoriesList');
    let html = `
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Category Name</th>
                        <th>Questions</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
    `;

    Object.entries(questions).forEach(([category, categoryQuestions]) => {
        html += `
            <tr>
                <td>${getCategoryName(category)}</td>
                <td>${categoryQuestions.length}</td>
                <td class="action-buttons">
                    <button class="btn btn-sm btn-outline-primary" onclick="editCategory('${category}')">
                        <i class="bi bi-pencil"></i>
                    </button>
                    <button class="btn btn-sm btn-outline-danger" onclick="deleteCategory('${category}')">
                        <i class="bi bi-trash"></i>
                    </button>
                </td>
            </tr>
        `;
    });

    html += `
                </tbody>
            </table>
        </div>
    `;

    categoriesList.innerHTML = html;
}

// Show modals
function showAddQuestionModal() {
    addQuestionModal.show();
}

function showAddCategoryModal() {
    addCategoryModal.show();
}

function showImportModal() {
    importModal.show();
}

// Add new question
function addQuestion() {
    const form = document.getElementById('addQuestionForm');
    const category = form.category.value;
    const question = form.question.value;
    const options = [
        form.option1.value,
        form.option2.value,
        form.option3.value,
        form.option4.value
    ];
    const correctAnswer = parseInt(form.correctAnswer.value);
    const explanation = form.explanation.value;

    // Generate new ID
    const newId = Math.max(...Object.values(questions).flat().map(q => q.id)) + 1;

    // Add question to questions object
    questions[category].push({
        id: newId,
        question,
        options,
        correctAnswer,
        explanation
    });

    // Refresh questions list
    loadQuestions();
    addQuestionModal.hide();
    form.reset();

    showAlert('Question added successfully!', 'success');
}

// Add new category
function addCategory() {
    const form = document.getElementById('addCategoryForm');
    const categoryName = form.categoryName.value;
    const categorySlug = categoryName.toLowerCase().replace(/[^a-z0-9]/g, '');

    // Check if category already exists
    if (questions[categorySlug]) {
        showAlert('Category already exists!', 'danger');
        return;
    }

    // Add new category
    questions[categorySlug] = [];

    // Refresh categories list
    loadCategories();
    addCategoryModal.hide();
    form.reset();

    showAlert('Category added successfully!', 'success');
}

// Import CSV
function importCSV() {
    const form = document.getElementById('importForm');
    const file = form.csvFile.files[0];

    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            const text = e.target.result;
            const rows = text.split('\n');
            let imported = 0;

            rows.forEach((row, index) => {
                if (index === 0) return; // Skip header row
                if (!row.trim()) return; // Skip empty rows

                const [question, option1, option2, option3, option4, correctAnswer, explanation, category] = row.split(',');
                const categorySlug = category.trim().toLowerCase().replace(/[^a-z0-9]/g, '');

                // Create category if it doesn't exist
                if (!questions[categorySlug]) {
                    questions[categorySlug] = [];
                }

                // Generate new ID
                const newId = Math.max(...Object.values(questions).flat().map(q => q.id)) + 1;

                // Add question
                questions[categorySlug].push({
                    id: newId,
                    question: question.trim(),
                    options: [option1.trim(), option2.trim(), option3.trim(), option4.trim()],
                    correctAnswer: parseInt(correctAnswer.trim()),
                    explanation: explanation.trim()
                });

                imported++;
            });

            // Refresh lists
            loadQuestions();
            loadCategories();
            importModal.hide();
            form.reset();

            showAlert(`${imported} questions imported successfully!`, 'success');
        };
        reader.readAsText(file);
    }
}

// Helper functions
function getCategoryName(slug) {
    return slug.split('-').map(word => 
        word.charAt(0).toUpperCase() + word.slice(1)
    ).join(' ');
}

function showAlert(message, type) {
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${type} alert-dismissible fade show position-fixed top-0 end-0 m-3`;
    alertDiv.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    document.body.appendChild(alertDiv);

    // Remove alert after 3 seconds
    setTimeout(() => {
        alertDiv.remove();
    }, 3000);
}

// Edit functions (demo only - shows alert)
function editQuestion(id) {
    showAlert('Edit functionality is not available in demo mode.', 'info');
}

function editCategory(category) {
    showAlert('Edit functionality is not available in demo mode.', 'info');
}

// Delete functions (demo only - shows alert)
function deleteQuestion(id) {
    showAlert('Delete functionality is not available in demo mode.', 'info');
}

function deleteCategory(category) {
    showAlert('Delete functionality is not available in demo mode.', 'info');
} 