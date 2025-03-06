<?php
session_start();

// Giriş yapılmamışsa login sayfasına yönlendir
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

// Güvenlik önlemi: db.php dosyasına doğrudan erişimi engelle
define('PROJECT_ROOT', true);

// Veritabanı bağlantı dosyasının yolunu düzelt
require '../includes/db.php';  // Bir üst dizindeki includes klasörüne git

// Hata raporlamayı aktif et (geliştirme aşamasında)
error_reporting(E_ALL);
ini_set('display_errors', 1);

try {
    // Kategorileri veritabanından çek
    $categories = $conn->query("SELECT * FROM categories")->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Veritabanı hatası: " . $e->getMessage());
}

// Kategori ekleme işlemi
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_category'])) {
    $categoryName = trim($_POST['category_name']);

    if (!empty($categoryName)) {
        try {
            $stmt = $conn->prepare("INSERT INTO categories (name) VALUES (?)");
            $stmt->execute([$categoryName]);
            echo "<div class='alert alert-success'>Kategori başarıyla eklendi!</div>";
        } catch (PDOException $e) {
            echo "<div class='alert alert-danger'>Kategori eklenirken bir hata oluştu: " . $e->getMessage() . "</div>";
        }
    } else {
        echo "<div class='alert alert-warning'>Kategori adı boş olamaz!</div>";
    }
}

// Kategori silme işlemi
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_category'])) {
    $categoryId = $_POST['category_id'];

    try {
        $stmt = $conn->prepare("DELETE FROM categories WHERE id = ?");
        $stmt->execute([$categoryId]);
        echo "<div class='alert alert-success'>Kategori başarıyla silindi!</div>";
    } catch (PDOException $e) {
        echo "<div class='alert alert-danger'>Kategori silinirken bir hata oluştu: " . $e->getMessage() . "</div>";
    }
}

// Kategori düzenleme işlemi
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_category'])) {
    $categoryId = $_POST['category_id'];
    $newCategoryName = trim($_POST['new_category_name']);

    if (!empty($newCategoryName)) {
        try {
            $stmt = $conn->prepare("UPDATE categories SET name = ? WHERE id = ?");
            $stmt->execute([$newCategoryName, $categoryId]);
            echo "<div class='alert alert-success'>Kategori başarıyla güncellendi!</div>";
        } catch (PDOException $e) {
            echo "<div class='alert alert-danger'>Kategori güncellenirken bir hata oluştu: " . $e->getMessage() . "</div>";
        }
    } else {
        echo "<div class='alert alert-warning'>Kategori adı boş olamaz!</div>";
    }
}

// Soru ekleme işlemi
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_question'])) {
    $question = $_POST['question'];
    $option1 = $_POST['option1'];
    $option2 = $_POST['option2'];
    $option3 = $_POST['option3'];
    $option4 = $_POST['option4'];
    $correctAnswer = $_POST['correct_answer'];
    $explanation = $_POST['explanation'];
    $categoryId = $_POST['category'];

    try {
        $stmt = $conn->prepare("INSERT INTO questions (question, option1, option2, option3, option4, correct_answer, explanation, category_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$question, $option1, $option2, $option3, $option4, $correctAnswer, $explanation, $categoryId]);
        echo "<div class='alert alert-success'>Soru başarıyla eklendi!</div>";
    } catch (PDOException $e) {
        echo "<div class='alert alert-danger'>Soru eklenirken bir hata oluştu: " . $e->getMessage() . "</div>";
    }
}

// CSV ile toplu soru ekleme işlemi
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['upload_csv'])) {
    if (isset($_FILES['csv_file']) && $_FILES['csv_file']['error'] === UPLOAD_ERR_OK) {
        $csvFile = $_FILES['csv_file']['tmp_name'];
        $errors = []; // Hatalı satırları tutacak dizi
        $successCount = 0; // Başarılı eklenen soru sayısı

        // CSV dosyasını aç
        if (($handle = fopen($csvFile, "r")) !== FALSE) {
            $header = fgetcsv($handle, 1000, ","); // Başlık satırını oku

            // CSV dosyasını satır satır oku
            while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                // Verileri kontrol et (8 sütun olmalı)
                if (count($data) === 8) {
                    $question = $data[0];
                    $option1 = $data[1];
                    $option2 = $data[2];
                    $option3 = $data[3];
                    $option4 = $data[4];
                    $correctAnswer = $data[5];
                    $explanation = $data[6];
                    $categoryId = $data[7];

                    // Veritabanına ekle
                    try {
                        $stmt = $conn->prepare("INSERT INTO questions (question, option1, option2, option3, option4, correct_answer, explanation, category_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                        $stmt->execute([$question, $option1, $option2, $option3, $option4, $correctAnswer, $explanation, $categoryId]);
                        $successCount++;
                    } catch (PDOException $e) {
                        // Hatalı satırı kaydet
                        $errors[] = "Hatalı satır: " . implode(", ", $data) . " - Hata: " . $e->getMessage();
                    }
                } else {
                    // Eksik veya hatalı satır
                    $errors[] = "Eksik veya hatalı satır: " . implode(", ", $data);
                }
            }
            fclose($handle);

            // Sonuçları göster
            if ($successCount > 0) {
                echo "<div class='alert alert-success'>$successCount soru başarıyla eklendi!</div>";
            }
            if (!empty($errors)) {
                echo "<div class='alert alert-danger'><strong>Hatalar:</strong><ul>";
                foreach ($errors as $error) {
                    echo "<li>$error</li>";
                }
                echo "</ul></div>";
            }
        } else {
            echo "<div class='alert alert-danger'>CSV dosyası açılamadı!</div>";
        }
    } else {
        echo "<div class='alert alert-danger'>Lütfen geçerli bir CSV dosyası seçin!</div>";
    }
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Paneli</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #f8f9fa; }
        .container { max-width: 800px; margin: 50px auto; padding: 0 15px; }
        .card { margin-bottom: 20px; border: none; box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1); }
        .card-title { color: #ff6f00; font-weight: bold; }
        .btn-primary { background-color: #ff6f00; border: none; }
        .btn-primary:hover { background-color: #e65a00; }
        .header-container { 
            display: flex; 
            justify-content: space-between; 
            align-items: center;
            margin-bottom: 30px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header-container">
            <h1>Admin Paneli</h1>
            <a href="logout.php" class="btn btn-danger">Çıkış Yap</a>
        </div>

        <!-- Kategori Ekleme Formu -->
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">Yeni Kategori Ekle</h5>
                <form method="POST">
                    <div class="mb-3">
                        <label for="category_name" class="form-label">Kategori Adı</label>
                        <input type="text" class="form-control" id="category_name" name="category_name" required>
                    </div>
                    <button type="submit" name="add_category" class="btn btn-primary">Kategori Ekle</button>
                </form>
            </div>
        </div>

        <!-- Kategori Listesi -->
        <h2 class="mt-5">Kategoriler</h2>
        <?php if (!empty($categories)): ?>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Kategori Adı</th>
                            <th>İşlemler</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($categories as $category): ?>
                            <tr>
                                <td><?= $category['id'] ?></td>
                                <td><?= $category['name'] ?></td>
                                <td>
                                    <!-- Düzenle Butonu -->
                                    <button type="button" class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#editCategoryModal<?= $category['id'] ?>">
                                        Düzenle
                                    </button>

                                    <!-- Sil Butonu -->
                                    <form method="POST" style="display:inline;">
                                        <input type="hidden" name="category_id" value="<?= $category['id'] ?>">
                                        <button type="submit" name="delete_category" class="btn btn-danger btn-sm">Sil</button>
                                    </form>
                                </td>
                            </tr>

                            <!-- Düzenleme Modalı -->
                            <div class="modal fade" id="editCategoryModal<?= $category['id'] ?>" tabindex="-1" aria-labelledby="editCategoryModalLabel<?= $category['id'] ?>" aria-hidden="true">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title" id="editCategoryModalLabel<?= $category['id'] ?>">Kategori Düzenle</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body">
                                            <form method="POST">
                                                <div class="mb-3">
                                                    <label for="new_category_name<?= $category['id'] ?>" class="form-label">Yeni Kategori Adı</label>
                                                    <input type="text" class="form-control" id="new_category_name<?= $category['id'] ?>" name="new_category_name" value="<?= $category['name'] ?>" required>
                                                </div>
                                                <input type="hidden" name="category_id" value="<?= $category['id'] ?>">
                                                <button type="submit" name="edit_category" class="btn btn-primary">Kaydet</button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="alert alert-warning">Henüz kategori bulunmamaktadır.</div>
        <?php endif; ?>

        <!-- Admin Paneline Soru Yönetimi Butonu Ekle -->
        <div class="text-center mt-4">
            <a href="questions.php" class="btn btn-success">Soruları Yönet</a>
        </div>

        <!-- Soru Ekleme Formu -->
        <h2 class="mt-5">Yeni Soru Ekle</h2>
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">Soru Ekle</h5>
                <form method="POST">
                    <div class="mb-3">
                        <label for="question" class="form-label">Soru</label>
                        <textarea class="form-control" id="question" name="question" required></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="option1" class="form-label">Seçenek 1</label>
                        <input type="text" class="form-control" id="option1" name="option1" required>
                    </div>
                    <div class="mb-3">
                        <label for="option2" class="form-label">Seçenek 2</label>
                        <input type="text" class="form-control" id="option2" name="option2" required>
                    </div>
                    <div class="mb-3">
                        <label for="option3" class="form-label">Seçenek 3</label>
                        <input type="text" class="form-control" id="option3" name="option3" required>
                    </div>
                    <div class="mb-3">
                        <label for="option4" class="form-label">Seçenek 4</label>
                        <input type="text" class="form-control" id="option4" name="option4" required>
                    </div>
                    <div class="mb-3">
                        <label for="correct_answer" class="form-label">Doğru Cevap</label>
                        <select class="form-select" id="correct_answer" name="correct_answer" required>
                            <option value="option1">Seçenek 1</option>
                            <option value="option2">Seçenek 2</option>
                            <option value="option3">Seçenek 3</option>
                            <option value="option4">Seçenek 4</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="explanation" class="form-label">Açıklama</label>
                        <textarea class="form-control" id="explanation" name="explanation" required></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="category" class="form-label">Kategori</label>
                        <select class="form-select" id="category" name="category" required>
                            <?php foreach ($categories as $category): ?>
                                <option value="<?= $category['id'] ?>"><?= $category['name'] ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <button type="submit" name="add_question" class="btn btn-primary">Soru Ekle</button>
                </form>
            </div>
        </div>

        <!-- CSV ile Toplu Soru Ekleme Formu -->
        <h2 class="mt-5">CSV ile Soru Ekle</h2>
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">CSV Dosyası Yükle</h5>
                <form method="POST" enctype="multipart/form-data">
                    <div class="mb-3">
                        <label for="csv_file" class="form-label">CSV Dosyası Seçin</label>
                        <input type="file" class="form-control" id="csv_file" name="csv_file" accept=".csv" required>
                    </div>
                    <button type="submit" name="upload_csv" class="btn btn-primary">Yükle ve İşle</button>
                </form>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>