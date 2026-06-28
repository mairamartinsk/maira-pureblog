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
    'custom_cover'      => '',
    'tags'      => [],
];

$images = [];
$imageFolder = PUREBLOG_BASE_PATH . '/content/images/book_covers';

if ($isEditing) {
        if (isset($books[$bookIndex])) {
        $book = array_merge($book, $books[$bookIndex]);
        }
    } else {
        $books[] = [
            'title'        => $book['title'],
            'author'       => $book['author'],
            'year_read'    => $book['year_read'],
            'reread'       => $book['reread'],
            'olid'         => $book['olid'],
            'custom_cover' => $book['custom_cover'],
            'tags'         => $book['tags']
        ];
    }

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['book_action']) && $_POST['book_action'] === 'upload_image') {
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $targetDir = PUREBLOG_BASE_PATH . '/content/images/book_covers';
        
        if (!is_dir($targetDir)) {
            @mkdir($targetDir, 0755, true);
        }
        
        $filename = $_FILES['image']['name'];
        $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        
        if (in_array($extension, ['jpg', 'jpeg', 'png', 'webp', 'gif'], true)) {
            $cleanName = slugify(pathinfo($filename, PATHINFO_FILENAME)) . '-' . time() . '.' . $extension;
            $destination = $targetDir . '/' . $cleanName;
            
            if (move_uploaded_file($_FILES['image']['tmp_name'], $destination)) {
                header('Location: ' . base_path() . '/admin/edit-book.php?id=' . $bookIdParam . '&uploaded=1');
                exit;
            } else {
                header('Location: ' . base_path() . '/admin/edit-book.php?id=' . $bookIdParam . '&upload_error=Failed to move uploaded file.');
                exit;
            }
        } else {
            header('Location: ' . base_path() . '/admin/edit-book.php?id=' . $bookIdParam . '&upload_error=Invalid file type.');
            exit;
        }
    }
    header('Location: ' . base_path() . '/admin/edit-book.php?id=' . $bookIdParam . '&upload_error=No file selected or upload error occurred.');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $book['title']  = trim($_POST['title'] ?? '');
    $book['author'] = trim($_POST['author'] ?? '');
    $book['olid']   = trim($_POST['olid'] ?? '');
    $book['custom_cover'] = trim($_POST['custom_cover'] ?? '');
    $book['reread'] = isset($_POST['reread']);
    $book['year_read'] = array_filter(array_map('intval', explode(',', $_POST['year_read'] ?? '')), function($val) {
        return $val >= 0;
    });
    $book['tags'] = array_filter(array_map('trim', explode(',', $_POST['tags'] ?? '')));

    if ($book['title'] === '') {
        $errors[] = "Book title is required.";
    }

    if (!$errors) {
        $updatedBook = [
            'title'     => $book['title'],
            'author'    => $book['author'],
            'year_read' => array_values($book['year_read']),
            'reread'    => $book['reread'],
            'olid'      => $book['olid'],
            'custom_cover'  => $book['custom_cover'],
            'tags'      => array_values($book['tags']),
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

if (is_dir($imageFolder)) {
    $files = glob($imageFolder . '/*.{jpg,jpeg,png,webp,gif}', GLOB_BRACE) ?: [];
    foreach ($files as $file) {
        if (is_file($file)) {
            $basename = basename($file);
            $url = base_path() . '/content/images/book_covers/' . $basename;
            $images[] = [
                'filename' => $basename,
                'url'      => $url,
            ];
        }
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

                    <label for="custom_cover">Custom Cover (leave OLID field empty)</label>
                    <input type="text" id="custom_cover" name="custom_cover" value="<?= e($book['custom_cover']) ?>" placeholder="eg: abc.jpg" autocomplete="off">

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
                        <span class="images-title">Re-read</span>
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
                    <form method="post" enctype="multipart/form-data" class="upload-form">
                        <input type="hidden" name="book_action" value="upload_image">
                        <?= csrf_field() ?>
                        <label class="hidden" id="image-upload-label" for="image"><?= e(t('admin.editor.upload_label')) ?></label>
                        <input type="file" id="image" name="image" accept="image/*" onchange="document.getElementById('upload-submit-btn').disabled = !this.value;">
                        <button type="submit" id="upload-submit-btn" disabled>
                            <svg class="icon" aria-hidden="true"><use href="#icon-upload"></use></svg>
                            <?= e(t('admin.editor.upload')) ?>
                        </button>
                    </form>
                        <?php if (!$images): ?>
                            <p><?= e(t('admin.editor.no_images')) ?></p>
                        <?php else: ?>
                            <p><?= e(t('admin.editor.attached_images')) ?></p>
                            <ul class="image-list">
                        <?php foreach ($images as $image): ?>
                            <li>
                                <div class="image-list-top">
                                    <img src="<?= e($image['url']) ?>" width="30" height="30" class="image-list-preview" alt="<?= e($image['filename']) ?>" loading="lazy" style="object-fit: cover; border-radius: 2px;"/>
                                    <code style="font-size: 0.8em; word-break: break-all;"><?= e($image['filename']) ?></code>
                                </div>
                                <div class="image-list-actions">
                                    <button type="button" class="link-button copy-image-url" data-url="<?= e($image['url']) ?>">
                                        <svg class="icon" aria-hidden="true"><use href="#icon-copy"></use></svg> Copy URL
                                    </button>
                                </div>
                            </li>
                        <?php endforeach; ?>
                        </ul>
                        <?php endif; ?>
                        </div>
                </section>
                <section class="sidebar-section">
                    <div class="section-divider">
                        <span class="images-title">Book Library</span>
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
        };
    </script>
    <script>
        document.addEventListener('click', function(e) {
            const btn = e.target.closest('.copy-markdown');
            if (btn) {
                const text = btn.getAttribute('data-markdown');
                navigator.clipboard.writeText(text).then(() => {
                    const originalText = btn.innerHTML;
                    btn.innerHTML = 'Copied!';
                    setTimeout(() => { btn.innerHTML = originalText; }, 2000);
                });
            }
        });
    </script>
    <script>
        document.addEventListener('click', function(e) {
            const btn = e.target.closest('.copy-image-url');
            if (btn) {
                const textToCopy = btn.getAttribute('data-url');
                
                navigator.clipboard.writeText(textToCopy).then(() => {
                    const originalHTML = btn.innerHTML;
                    btn.innerHTML = 'Copied!';
                    btn.style.color = '#28a745';
                    
                    setTimeout(() => {
                        btn.innerHTML = originalHTML;
                        btn.style.color = '';
                    }, 2000);
                }).catch(err => {
                    console.error('Could not copy image path: ', err);
                });
            }
        });
    </script>
<?php require __DIR__ . '/../includes/admin-footer.php'; ?>