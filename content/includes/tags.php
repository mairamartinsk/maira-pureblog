<?php

declare(strict_types=1);

require_once __DIR__ . './../../functions.php';

require_setup_redirect();

$config = load_config();
$fontStack = font_stack_css($config['theme']['font_stack'] ?? 'sans');
$pageTitle = 'Tags';
$metaDescription = '';

require __DIR__ . './../../includes/header.php';
render_masthead_layout($config, ['page' => $page ?? null]);

$jsonFile = 'content/tag-index.json';
$jsonData = file_get_contents($jsonFile);
$tags = json_decode($jsonData, true);

// Check if decoding was successful
if ($tags === null && json_last_error() !== JSON_ERROR_NONE) {
    die("Error decoding JSON: " . json_last_error_msg());
}

ksort($tags);
?>
    <main>
        <h1>Tags</h1>
        <ul class="tag-cloud">
        <?php foreach ($tags as $tag => $posts): ?>
          <li>
            <a class="button" href="/tag/<?= urlencode($tag); ?>">
                <?= htmlspecialchars($posts['name']); ?> <small>(<?= count($posts['posts']) ?>)</small>
            </a>
        </li>
        <?php endforeach; ?>
        <ul>
    </main>
    <?php render_footer_layout($config, ['page' => $page ?? null]); ?>
</body>
</html>