<?php

declare(strict_types=1);

$post = $post ?? [];
?>

<div class="post-upvote">
    <button
        class="iine-button"
        data-icon="heart"
        aria-hidden="true">
    </button>
</div>

<?php if (!empty($post['tags'])): ?>
    <p class="tag-list"><svg class="icon" aria-hidden="true"><use href="/assets/icons/sprite.svg#icon-tag"></use></svg> <?= render_tag_links($post['tags']) ?></p>
<?php endif; ?>

<div class="post-nav">
  <div>
    <?php if (!empty($previous_post)): ?>
      <p>⇠ Previous post<br>
      <a class="pagination-links" href="/<?= e((string) ($previous_post['slug'] ?? '')) ?>">
        <?= e((string) ($previous_post['title'] ?? '')) ?>
      </a></p>
    <?php endif; ?>
  </div>

  <div class="post-nav-next">
    <?php if (!empty($next_post)): ?>
      <p>Next post ⇢<br>
      <a class="pagination-links" href="/<?= e((string) ($next_post['slug'] ?? '')) ?>">
        <?= e((string) ($next_post['title'] ?? '')) ?>
      </a></p>
    <?php endif; ?>
  </div>
</div>

<div class="post-comments">
    <h2>Comments</h2>
    <div class="mail">
      <a class="button reply-button"href="mailto:{{ site_email }}?subject=Reply to: {{ post_title }}">✉️ Reply by email</a>
      <p></p>
    </div>
    
    <div id="comments" data-post-slug="<?= $post['slug'] ?>">
</div>
<script src="https://comments.mairamartins.com/public/embed.js" defer></script>