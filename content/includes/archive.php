<?php

declare(strict_types=1);

require_once __DIR__ . './../../functions.php';

require_setup_redirect();

$config = load_config();
$fontStack = font_stack_css($config['theme']['font_stack'] ?? 'sans');
$pageTitle = 'Archive';
$metaDescription = '';

require __DIR__ . './../../includes/header.php';
render_masthead_layout($config, ['page' => $page ?? null]);

$posts = get_all_posts(false);
$postsByYear = [];

foreach ($posts as $post) {
    // Extract the year from the date (e.g., "2026-02-16 00:00" -> "2026")
    $year = date('Y', strtotime($post['date']));

    if (!isset($postsByYear[$year])) {
        $postsByYear[$year] = [];
    }
    $postsByYear[$year][] = $post;
}

krsort($postsByYear);
?>

<main>
    <h1>Archive</h1>
		<p><?= count($posts) ?> published posts.</p>

		<?php foreach ($postsByYear as $year => $yearPosts): ?>
    <div class="archive">
        <h2><?= $year; ?> <span>(<?= count($yearPosts); ?>)</span></h2>
        <ul>
            <?php foreach ($yearPosts as $post): ?>
                <li>
                    <a href="/<?= $post['slug']; ?>">
                        <?= htmlspecialchars($post['title']) ?>
                    </a> 
                    <small><time><?= e(format_post_date_for_display((string) ($post['date'] ?? ''), $config ?? [])) ?></time></small>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endforeach; ?>
	</main>
<?php render_footer_layout($config, ['page' => $page ?? null]); ?>
</body>
</html>