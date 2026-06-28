<?php
declare(strict_types=1);
require __DIR__ . '/bootstrap.php';

$config = load_config();
$fontStack = font_stack_css($config['theme']['admin_font_stack'] ?? 'sans');

$errors = [];
$bookIdParam = $_GET['id'] ?? '';
$isEditing = $bookIdParam !== '';
$bookIndex = $isEditing ? (int)$bookIdParam : -1;

// Use the functions autoloaded from content/functions.php
$books = load_books_yaml();

$book = [
    'title'     => '',
    'author'    => '',
    'year_read' => [],
    'reread'    => false,
    'olid'      => '',
    'tags'      => [],
    'custom_cover' => ''
];

if ($isEditing) {
    if (isset($books[$bookIndex])) {
        $book = array_merge($book, $books[$bookIndex]);
    } else {
        $errors[] = "Book entry not found.";
        $isEditing = false;
    }
}

$autosaveData = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $book['title']  = trim($_POST['title'] ?? '');
    $book['author'] = trim($_POST['author'] ?? '');
    $book['olid']   = trim($_POST['olid'] ?? '');
    $book['reread'] = isset($_POST['reread']);
    $book['year_read'] = array_filter(array_map('intval', explode(',', $_POST['year_read'] ?? '')), function($val) {
        return $val >= 0;
    });
    $book['tags'] = array_filter(array_map('trim', explode(',', $_POST['tags'] ?? '')));
    $book['custom_cover']   = trim($_POST['custom_cover'] ?? '');

    $imageFolder = '';
    if ($book['title'] !== '') {
        $imageFolder = __DIR__ . '/../content/images/book_covers';
        if (is_dir($imageFolder)) {
            $files = glob($imageFolder . '/*') ?: [];
            foreach ($files as $file) {
                if (is_file($file)) {
                    $basename = basename($file);
                    $altText = pathinfo($basename, PATHINFO_FILENAME) ?: 'image';
                    $url = base_path() . '/content/images/book_covers/' . $basename;
                    $images[] = [
                        'filename' => $basename,
                    ];
                }
            }
        }
    }

    if ($book['title'] === '') {
        $errors[] = "Book title is required.";
    }

    if (!$errors) {
        $updatedBook = [
            'title'     => $book['title'],
            'author'    => $book['author'],
            'year_read' => array_values($book['year_read
    ']),
            'reread'    => $book['rereas'],
            'olid'      => $book['olid'],
            'tags'      => array_values($book['tags']),
            'custom_cover'     => $book['custom_cover']
        ];

        if ($isEditing && $bookIndex >= 0) {
            $books[$bookIndex] = $updatedBook;
        } else {
            $books[] = $updatedBook;
            $bookIndex = count($books) - 1;
        }

        save_books_yaml($books);

        header('Location: ' . base_path() . '/admin/edit-book.php?id=' . $bookIndex . '&saved=1');
        exit;
    }
}

$adminTitle = $isEditing ? "Edit Book" : "Add New Book";
require __DIR__ . '/../includes/admin-head.php';
?>
    <main>
        <div class="editor-grid" id="book-editor-grid">
            <section class="editor-main">
                <h1>Book Editor</h1>
                <?php if ($errors): ?>
                    <div class="notice delete"><ul><?php foreach ($errors as $e): ?><li><?= e($e) ?></li><?php endforeach; ?></ul></div>
                <?php endif; ?>
                <?php if (!empty($_GET['saved'])): ?>
                    <p class="notice" data-auto-dismiss>Book tracking data updated!</p>
                <?php endif; ?>
                <?php if (!empty($_GET['uploaded'])): ?>
                    <p class="notice" data-auto-dismiss><?= e(t('admin.editor.notice_image_uploaded')) ?></p>
                <?php endif; ?>
                <?php if (!empty($_GET['upload_error'])): ?>
                    <p class="notice delete" data-auto-dismiss><?= e($_GET['upload_error']) ?></p>
                <?php endif; ?>
                <form method="post" class="editor-form" id="book-form">
                    <input type="hidden" id="book-cover-value" name="book_cover" value="<?= e($book['custom_cover'] ?? '') ?>">
                    <?= csrf_field() ?>

                    <nav class="editor-actions">
                        <button class="save" type="submit" form="book-form" aria-label="Save book">
                            <svg class="icon" aria-hidden="true"><use href="#icon-save"></use></svg>
                            Save book
                        </button>
                        <a href="?action=new" class="link-button">+ Add Another</a>
                    </nav>

                    <label for="title">Book Title</label>
                    <input type="text" id="title" name="title" value="<?= e($book['title']) ?>" required autocomplete="off" >

                    <label for="author">Book Author</label>
                    <input type="text" id="author" name="author" value="<?= e($book['author']) ?>" autocomplete="off">

                    <label for="olid">Open Library ID (for book cover images)</label>
                    <input type="text" id="olid" name="olid" value="<?= e($book['olid']) ?>" placeholder="eg: 9780261102217" autocomplete="off">

                    <label for="year_read">Years read (comma separated if re-read)</label>
                    <input type="text" id="year_read" name="year_read" value="<?= e(implode(', ', $book['year_read'])) ?>" placeholder="eg: 2016, 2021 (Use 0 for before 2015)" autocomplete="off">

                    <label for="tags">Tags / Book series (comma separated)</label>
                    <input type="text" id="tags" name="tags" value="<?= e(implode(', ', $book['tags'])) ?>" placeholder="eg: Trading, Agatha Christie, Cookbooks" autocomplete="off">
                </form>
            </section>
            <aside class="editor-sidebar">
                <div class="sidebar-header">
                    <button type="button" class="sidebar-toggle" id="sidebar-toggle-tab" aria-label="Book settings" title="Book settings" onclick="document.body.classList.toggle('editor-sidebar-open');">
                        <svg class="icon close-icon" aria-hidden="true"><use href="#icon-panel-right-close"></use></svg>
                        <svg class="icon open-icon" aria-hidden="true"><use href="#icon-settings"></use></svg>
                    </button>
                </div>
                
                <section class="sidebar-section">
                    <div class="section-divider">
                        <div>
                            <label for="reread">
                                <input type="checkbox" id="reread" name="reread" <?= $book['reread'] ? 'checked' : '' ?> form="book-form">
                                Re-read?
                            </label>
                        </div>
                    </div>
                </section>

                <section class="sidebar-section">
                    <div class="section-divider">
                    <span class="images-title"><?= e(t('admin.editor.images_title')) ?></span>
                    <form method="post" action="<?= base_path() ?>/admin/upload-image.php" enctype="multipart/form-data" class="upload-form">
                        <input type="hidden" name="slug" value="book_covers">
                        <input type="hidden" name="editor_type" value="page">
                        <?= csrf_field() ?>
                        <label class="hidden" for="image"><?= e(t('admin.editor.upload_label')) ?></label>
                        <input type="file" id="image" name="image" accept="image/*,.avif">
                        <button type="submit" disabled>
                            <svg class="icon" aria-hidden="true"><use href="#icon-upload"></use></svg>
                            <?= e(t('admin.editor.upload')) ?>
                        </button>
                </form>
                        <?php if (!$images): ?>
                            <p><?= e(t('admin.editor.no_images')) ?></p>
                        <?php else: ?>
                            <p><?= e(t('admin.editor.attached_images')) ?></p>
                            <ul class="image-list">
                            <?php foreach ($images as $image):
                                $imageRawPath = '/content/images/book_covers/' . $image['filename'];
                            ?>
                                <li>
                                    <div class="image-list-top">
                                        <img src="<?= e($image['url']) ?>" width="30" height="30" class="image-list-preview" alt="<?= e($image['filename']) ?>" loading="lazy"/>
                                        <code><?= e($image['filename']) ?></code>
                                    </div>
                                    <div class="image-list-actions">
                                        <button type="button" class="link-button copy-markdown" data-markdown="<?= e($image['markdown']) ?>"><svg class="icon" aria-hidden="true"><use href="#icon-copy"></use></svg> <?= e(t('admin.editor.copy')) ?></button>
                                        <form method="post" action="<?= base_path() ?>/admin/delete-image.php" class="inline-form" onsubmit="return confirm('<?= e(t('admin.page_editor.delete_image_confirm')) ?>');">
                                            <input type="hidden" name="slug" value="<?= e($page['slug']) ?>">
                                            <input type="hidden" name="editor_type" value="page">
                                            <input type="hidden" name="filename" value="<?= e($image['filename']) ?>">
                                            <?= csrf_field() ?>
                                            <button type="submit" class="link-button delete"><svg class="icon" aria-hidden="true"><use href="#icon-circle-x"></use></svg> <?= e(t('admin.editor.delete')) ?></button>
                                        </form>
                                    </div>
                                </li>
                            <?php endforeach; ?>
                            </ul>
                        <?php endif; ?>
                        </div>
                </section>
                <section class="sidebar-section">
                    <div class="section-divider">
                        <ul>
                            <?php foreach ($books as $idx => $b): ?>
                                <li>
                                    <span>
                                        <?= e($b['title']) ?>
                                    </span>
                                    <a href="?id=<?= $idx ?>">Edit</a>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </section>
            </aside>
        </div>
    </main>
    <script>
        window.PureblogEditorConfig = {
            editorType: 'book',
            formId: 'book-form',
            csrfToken: '<?= e(csrf_token()) ?>',
            basePath: '<?= e(base_path()) ?>',
            autosave: <?= $autosaveData !== null ? json_encode($autosaveData, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT) : 'null' ?>,
            strings: <?= json_encode([
                'autosaving'        => t('admin.editor.js_autosaving'),
                'autosaved'         => t('admin.editor.js_autosaved'),
                'autosave_failed'   => t('admin.editor.js_autosave_failed'),
                'autosave_banner'   => t('admin.editor.js_autosave_banner'),
                'view'              => t('admin.editor.js_view'),
                'hide'              => t('admin.editor.js_hide'),
                'restore'           => t('admin.editor.js_restore'),
                'discard'           => t('admin.editor.js_discard'),
                'title_label'       => t('admin.editor.js_title_label'),
                'save_failed'       => t('admin.editor.js_save_failed'),
                'save_before_upload'=> t('admin.editor.js_save_before_upload'),
                'copied'            => t('admin.editor.js_copied'),
                'copy'              => t('admin.editor.copy'),
                'copy_failed'       => t('admin.editor.js_copy_failed'),
                'save_post_first'       => t('admin.editor.js_save_post_first'),
                'save_page_first'       => t('admin.editor.js_save_page_first'),
                'upload_failed'         => t('admin.editor.js_upload_failed'),
                'feature_image_confirm' => t('admin.editor.js_feature_image_confirm'),
                'feature_image_failed'  => t('admin.editor.js_feature_image_failed'),
            ], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE) ?>,
        };
    </script>
    <script type="module" src="<?= base_path() ?>/admin/js/editor.js?v=<?= e((string) @filemtime(__DIR__ . '/js/editor.js')) ?>"></script>
<?php require __DIR__ . '/../includes/admin-footer.php'; ?>