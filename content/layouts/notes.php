<article class="layout">
    <div class="notes">
    <?php if (isset($post['displayTitle']) && $post['displayTitle'] === '1'): ?>
        <h1><?= e($post['title']) ?></h1>
    <?php endif; ?>
    <?= render_markdown($post['content']) ?>
    <?php if ($post['date']): ?>
                    <p class="post-date"><svg class="icon" aria-hidden="true"><use href="#icon-calendar"></use></svg> <time datetime="<?= e(format_datetime_for_display((string) $post['date'], $config, 'c')) ?>"><?= e(format_post_date_for_display((string) $post['date'], $config)) ?></time></p>
                <?php endif; ?>
    </div>
    <?= render_post_navigation() ?>
</article>