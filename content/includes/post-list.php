<?php
// Expects: $posts, $postListLayout, $currentPage, $totalPages, $paginationBase
// Optional: $paginationQueryParams (associative array of extra query params to preserve)
$paginationQueryParams =
    isset($paginationQueryParams) && is_array($paginationQueryParams)
        ? $paginationQueryParams
        : []; ?>
<?php if (!$posts): ?>
    <p><?= e(t("frontend.no_posts")) ?></p>
<?php elseif ($postListLayout === "archive"): ?>
    <!-- Archive view -->
    <div class="archive-list">
        <?php foreach ($posts as $post): ?>
            <time datetime="<?= e(
                format_datetime_for_display(
                    (string) ($post["date"] ?? ""),
                    $config ?? [],
                    "c",
                ),
            ) ?>"><?= e(
    format_post_date_for_display((string) ($post["date"] ?? ""), $config ?? []),
) ?></time>
            <a href="<?= base_path() ?>/<?= e($post["slug"]) ?>"><?= e(
    $post["title"],
) ?></a>
        <?php endforeach; ?>
    </div>
<?php else: ?>
    <?php foreach ($posts as $post): ?>
        <article class="post-item">
            <!-- Excerpt view -->
            <?php if ($postListLayout === "excerpt"): ?>

                <!-- Custom layouts -->
                <div class="excerpt-view <?= isset($post["layout"])
                    ? $post["layout"]
                    : "" ?>">
                    <?php if (isset($post["layout"])): ?>

                        <!-- Notes layout -->
                        <?php if ($post["layout"] == "notes"): ?>
                             <h2 class="<?= isset($post["displayTitle"]) &&
                             $post["displayTitle"] === "1"
                                 ? "visible"
                                 : "hidden" ?>"><a href="<?= base_path() ?>/<?= e(
    $post["slug"],
) ?>"><?= e($post["title"]) ?></a></h2>
                            <?= render_markdown($post["content"], [
                                "post_title" => (string) ($post["title"] ?? ""),
                            ]) ?>
                        <?php endif; ?>

                        <!-- Photos layout -->
                        <?php if ($post["layout"] == "photos"): ?>
                            <h2><a href="<?= base_path() ?>/<?= e(
    $post["slug"],
) ?>"><?= e($post["title"]) ?></a></h2>
                            <?= render_markdown($post["content"], [
                                "post_title" => (string) ($post["title"] ?? ""),
                            ]) ?>
                        <?php endif; ?>

                        <!-- Featured image layout -->
                         <?php if ($post["layout"] === "featured"): ?>
                            <h2><a href="<?= base_path() ?>/<?= e(
    $post["slug"],
) ?>"><?= e($post["title"]) ?></a></h2>
                    <?php
                    $excerptSource = trim(
                        (string) ($post["description"] ?? ""),
                    );
                    if ($excerptSource === "") {
                        $excerptSource = get_excerpt($post["content"]);
                    }
                    ?>
                    <img alt="<?= $post["title"] ?>" src="<?= $post[
    "featuredImage"
] ?>" loading="lazy">
                        <p class="post-excerpt"><?= e($excerptSource) ?></p>
                        <?php if (!empty($post["tags"])): ?>
                            <p class="tag-list"><?= render_tag_links(
                                $post["tags"],
                            ) ?></p>
                        <?php endif; ?>

                    <?php endif; ?>
                    <?php else: ?>
                    <!-- Default layout (excerpts) -->
                    <h2><a href="<?= base_path() ?>/<?= e(
    $post["slug"],
) ?>"><?= e($post["title"]) ?></a></h2>
                    <?php
                    $excerptSource = trim(
                        (string) ($post["description"] ?? ""),
                    );
                    if ($excerptSource === "") {
                        $excerptSource = get_excerpt($post["content"]);
                    }
                    ?>
                        <p class="post-excerpt"><?= e($excerptSource) ?></p>
                        <?php if (!empty($post["tags"])): ?>
                            <p class="tag-list"><?= render_tag_links(
                                $post["tags"],
                            ) ?></p>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            
            <!-- Full post view -->
            <?php elseif ($postListLayout === "full"): ?>
                <div class="full-post-view">
                    <h1><a href="<?= base_path() ?>/<?= e(
    $post["slug"],
) ?>"><?= e($post["title"]) ?></a></h1>
                    <?php if ($post["date"]): ?>
                        <p class="post-date"><time datetime="<?= e(
                            format_datetime_for_display(
                                (string) $post["date"],
                                $config ?? [],
                                "c",
                            ),
                        ) ?>"><?= e(
    format_post_date_for_display((string) $post["date"], $config ?? []),
) ?></time></p>
                    <?php endif; ?>
                    <?= render_markdown($post["content"], [
                        "post_title" => (string) ($post["title"] ?? ""),
                    ]) ?>
                    <?php if (!empty($post["tags"])): ?>
                        <p class="tag-list"><?= render_tag_links(
                            $post["tags"],
                        ) ?></p>
                    <?php endif; ?>
                    <hr>
                </div>
            <?php endif; ?>
        </article>
    <?php endforeach; ?>
<?php endif; ?>
<?php if (($posts ?? []) && $totalPages > 1): ?>
    <nav class="pagination">
        <?php if ($currentPage > 1): ?>
            <?php
            $prevParams = array_merge($paginationQueryParams, [
                "page" => (string) ($currentPage - 1),
            ]);
            $prevHref =
                e($paginationBase) . "?" . e(http_build_query($prevParams));
            ?>
            <a href="<?= $prevHref ?>"><?= e(
    t("frontend.pagination_newer"),
) ?></a>
        <?php endif; ?>
        <?php if ($currentPage < $totalPages): ?>
            <?php
            $nextParams = array_merge($paginationQueryParams, [
                "page" => (string) ($currentPage + 1),
            ]);
            $nextHref =
                e($paginationBase) . "?" . e(http_build_query($nextParams));
            ?>
            <a href="<?= $nextHref ?>"><?= e(
    t("frontend.pagination_older"),
) ?></a>
        <?php endif; ?>
    </nav>
<?php endif; ?>
