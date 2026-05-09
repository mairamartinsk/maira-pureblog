<article class="layout">
    <div class="featured">
            <h1><?= e($post["title"]) ?></h1>
            <?php if ($post["date"]): ?>
                <p class="post-date"><time datetime="<?= e(
                    format_datetime_for_display(
                        (string) $post["date"],
                        $config,
                        "c",
                    ),
                ) ?>"><?= e(
    format_post_date_for_display((string) $post["date"], $config),
) ?></time></p>
                <?php endif; ?>
			<?= render_markdown($post["content"]) ?>
    </div>
    <?= render_layout_partial('post-meta', [
                    'post' => $post,
                    'config' => $config,
                    'post_title' => (string) ($post['title'] ?? ''),
                    'content_title' => (string) ($post['title'] ?? ''),
                    'previous_post' => $adjacentPosts['previous'] ?? null,
                    'next_post' => $adjacentPosts['next'] ?? null,
                ]) ?>
</article>