<?php

declare(strict_types=1);

$tagIndex  = load_tag_index();
$originalNames = [];
$tagCounts = [];
if ($tagIndex) {
    foreach ($tagIndex as $slug => $entry) {
        $originalNames[$slug] = $entry['name'];
        $tagCounts[$slug] = is_array($entry['posts'] ?? null) ? count($entry['posts']) : count($entry);
    }
}
uksort($tagCounts, function (string $a, string $b) use ($originalNames): int {
    return strcasecmp($originalNames[$a] ?? $a, $originalNames[$b] ?? $b);
});
$maxCount = $tagCounts ? max($tagCounts) : 1;
$minCount = $tagCounts ? min($tagCounts) : 1;
$range    = $maxCount > $minCount ? $maxCount - $minCount : 1;

$pageTitle       = 'Tag Cloud';
$metaDescription = 'Browse all the tags on my site.';

require PUREBLOG_BASE_PATH . '/includes/header.php';
render_masthead_layout($config, ['page' => null]);
?>
<main>
    <h1>Tags</h1>

    <?php if (empty($tagCounts)): ?>
        <p>No tags found.</p>
    <?php else: ?>
        <ul class="tag-cloud">
            <?php foreach ($tagCounts as $slug => $count):
                $name     = $originalNames[$slug] ?? $slug;
                echo '<li><a href="/tag/' . e(rawurlencode((string) $slug)) . '"'
                   . ' class="button">'
                   . e($name) . ' <small>(' . e((string) $count) . ')</small>' . '</a></li>' . '  ';
            endforeach; ?>
        </ul>
    <?php endif; ?>
</main>
    <?php render_footer_layout($config, ['page' => $page ?? null]); ?>
</body>
</html>