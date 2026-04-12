<?php
declare(strict_types=1);

if (!function_exists('font_stack_css') || !function_exists('require_setup_redirect')) {
    header('Location: ' . (function_exists('base_path') ? base_path() : '') . '/');
    exit;
}

$fontStack = $fontStack ?? font_stack_css($config['theme']['font_stack'] ?? 'sans');
$pageTitle = $pageTitle ?? ($page['title'] ?? t('frontend.page_not_found'));
$metaDescription = $metaDescription ?? (!empty($page['description']) ? $page['description'] : '');

$searchPageSlug = trim((string) ($config['search_page_slug'] ?? 'search'));

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

?>
<?php if ($__p = find_include('header')) require $__p; ?>
<?php render_masthead_layout($config, ['page' => $page ?? null]); ?>
    <main>
            <?php
            $isSearchPage = $searchPageSlug !== '' && ($page['slug'] ?? '') === $searchPageSlug;
            ?>
            <article>
<?php
                    $query = trim($_GET['q'] ?? '');
                    $index = load_search_index();
                    $sourcePosts = $index ?? get_all_posts(false);
                    $filteredPosts = filter_posts_by_query($sourcePosts, $query);
                    if ($index !== null && $filteredPosts) {
                        $hydrated = [];
                        foreach ($filteredPosts as $post) {
                            $slug = (string) ($post['slug'] ?? '');
                            if ($slug === '') continue;
                            $fullPost = get_post_by_slug($slug, false);
                            if ($fullPost) $hydrated[] = $fullPost;
                        }
                        $filteredPosts = $hydrated;
                    }
                    $perPage = (int) ($config['posts_per_page'] ?? 20);
                    $currentPage = (int) ($_GET['page'] ?? 1);
                    $pagination = paginate_posts($filteredPosts, $perPage, $currentPage);
                    $posts = $pagination['posts'];
                    $totalPosts = $pagination['totalPosts'];
                    $totalPages = $pagination['totalPages'];
                    $currentPage = $pagination['currentPage'];
                    $postListLayout = $config['theme']['post_list_layout'] ?? 'excerpt';
                    $paginationBase = base_path() . '/' . $searchPageSlug;
                    $paginationQueryParams = $query !== '' ? ['q' => $query] : [];
                    ?>
                    <section class="site-search">
                        <form class="site-search-form" method="get" action="<?= e(base_path() . '/' . $searchPageSlug) ?>">
                            <label class="hidden" for="search-query"><?= e(t('frontend.search_label')) ?></label>
                            <input type="search" id="search-query" name="q" value="<?= e($query) ?>" placeholder="<?= e(t('frontend.search_placeholder')) ?>">
                            <button type="submit"><?= e(t('frontend.search_button')) ?></button>
                        </form>
                        <?php if ($query === ''): ?>
                            <p><?= e(t('frontend.search_empty')) ?></p>
                        <?php elseif (!$filteredPosts): ?>
                            <p><?= e(t('frontend.no_posts_found', ['search' => $query])) ?></p>
                        <?php else: ?>
                            <p><?= e($totalPosts === 1 ? t('frontend.search_result', ['n' => $totalPosts]) : t('frontend.search_results', ['n' => $totalPosts])) ?></p>
                            <?php if ($__p = find_include('post-list')) require $__p; ?>
                        <?php endif; ?>
                    </section>

										<section>
											<?php if (empty($tagCounts)): ?>
        <p>No tags found.</p>
    <?php else: ?>
			<h2>Tags</h2>
        <ul class="tag-cloud">
            <?php foreach ($tagCounts as $slug => $count):
                $name     = $originalNames[$slug] ?? $slug;
                echo '<li><a href="/tag/' . e(rawurlencode((string) $slug)) . '"'
                   . ' class="button">'
                   . e($name) . ' <small>(' . e((string) $count) . ')</small>' . '</a></li>' . '  ';
            endforeach; ?>
        </ul>
    <?php endif; ?>
										</section>
										            </article>
    </main>
    <?php render_footer_layout($config, ['page' => $page ?? null]); ?>
</body>
</html>