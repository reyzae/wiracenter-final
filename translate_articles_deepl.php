<?php
require_once 'config/config.php';
require_once 'config/database.php';

$apiKey = '87191d57-2a03-4488-9ca9-ae6a8f1167d6'; // API key DeepL kamu

$db = new Database();
$conn = $db->getConnection();

// Ambil semua artikel yang belum punya content_en
$stmt = $conn->prepare("SELECT id, title, content, excerpt FROM articles WHERE (title_en IS NULL OR title_en = '') OR (content_en IS NULL OR content_en = '') LIMIT 10");
$stmt->execute();
$articles = $stmt->fetchAll(PDO::FETCH_ASSOC);

function deepl_translate($text, $targetLang, $apiKey) {
    if (empty(trim($text))) return '';
    $url = "https://api-free.deepl.com/v2/translate";
    $data = [
        'auth_key' => $apiKey,
        'text' => $text,
        'target_lang' => strtoupper($targetLang)
    ];
    $options = [
        'http' => [
            'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
            'method'  => 'POST',
            'content' => http_build_query($data),
            'timeout' => 20
        ]
    ];
    $context  = stream_context_create($options);
    $result = file_get_contents($url, false, $context);
    if ($result === FALSE) {
        $error = error_get_last();
        print_r($error);
        echo "Gagal request ke DeepL untuk: $text\n";
        return '';
    }
    $json = json_decode($result, true);
    print_r($json);
    return $json['translations'][0]['text'] ?? '';
}

foreach ($articles as $article) {
    $id = $article['id'];
    $title_en = deepl_translate($article['title'], 'EN', $apiKey);
    $content_en = deepl_translate($article['content'], 'EN', $apiKey);
    $excerpt_en = deepl_translate($article['excerpt'], 'EN', $apiKey);

    // Debug print hasil terjemahan
    echo "Artikel ID $id\n";
    echo "Title EN: $title_en\n";
    echo "Content EN: $content_en\n";
    echo "Excerpt EN: $excerpt_en\n";

    // Update ke database
    $update = $conn->prepare("UPDATE articles SET title_en = ?, content_en = ?, excerpt_en = ? WHERE id = ?");
    $update->execute([$title_en, $content_en, $excerpt_en, $id]);
    echo "Artikel ID $id berhasil diterjemahkan.\n";
}

echo "Selesai!\n";
?>