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
    'tags'      => []
];

if ($isEditing) {
    if (isset($books[$bookIndex])) {
        $book = array_merge($book, $books[$bookIndex]);
    } else {
        $errors[] = "Book entry not found.";
        $isEditing = false;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titleInput  = trim($_POST['title'] ?? '');
    $authorInput = trim($_POST['author'] ?? '');
    $olidInput   = trim($_POST['olid'] ?? '');
    $rereadInput = isset($_POST['reread']);
    
    $yearsArray = array_filter(array_map('intval', explode(',', $_POST['year_read'] ?? '')), function($val) {
        return $val >= 0;
    });
    
    $tagsArray = array_filter(array_map('trim', explode(',', $_POST['tags'] ?? '')));

    if ($titleInput === '') {
        $errors[] = "Book title is required.";
    }

    if (!$errors) {
        $updatedBook = [
            'title'     => $titleInput,
            'author'    => $authorInput,
            'year_read' => array_values($yearsArray),
            'reread'    => $rereadInput,
            'olid'      => $olidInput,
            'tags'      => array_values($tagsArray)
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
        <div class="editor-grid">
            <section class="editor-main">
                <h1>Book Editor Dashboard</h1>
                <?php if ($errors): ?>
                    <div class="notice delete"><ul><?php foreach ($errors as $e): ?><li><?= e($e) ?></li><?php endforeach; ?></ul></div>
                <?php endif; ?>
                <?php if (!empty($_GET['saved'])): ?>
                    <p class="notice" data-auto-dismiss>Book tracking data updated!</p>
                <?php endif; ?>

                <form method="post" class="editor-form" id="book-form">
                    <?= csrf_field() ?>
                    <nav class="editor-actions">
                        <button class="save" type="submit" form="book-form">
                            <svg class="icon" aria-hidden="true"><use href="#icon-save"></use></svg> Save Book
                        </button>
                        <a href="?action=new" class="link-button" style="margin-left:10px; display:inline-flex; align-items:center;">+ Add Another</a>
                    </nav>

                    <label for="title">Book Title</label>
                    <input type="text" id="title" name="title" value="<?= e($book['title']) ?>" required autocomplete="off">

                    <label for="author">Author Name</label>
                    <input type="text" id="author" name="author" value="<?= e($book['author']) ?>" autocomplete="off">

                    <label for="olid">Open Library ID (for bookshelf cover images)</label>
                    <input type="text" id="olid" name="olid" value="<?= e($book['olid']) ?>" placeholder="e.g., 9780261102217" autocomplete="off">

                    <label for="year_read">Years Read (Comma separated if re-read)</label>
                    <input type="text" id="year_read" name="year_read" value="<?= e(implode(', ', $book['year_read'])) ?>" placeholder="e.g., 2016, 2021 (Use 0 for Before 2015)" autocomplete="off">

                    <label for="tags">Collection Tags / Projects (Comma separated)</label>
                    <input type="text" id="tags" name="tags" value="<?= e(implode(', ', $book['tags'])) ?>" placeholder="e.g., Trading, Agatha Christie Project, Cookbooks" autocomplete="off">

                    <div style="margin-top:20px;">
                        <label for="reread" style="display:inline-flex; align-items:center; cursor:pointer; font-weight:normal;">
                            <input type="checkbox" id="reread" name="reread" <?= $book['reread'] ? 'checked' : '' ?> style="margin-right:8px;">
                            Mark as Re-read
                        </label>
                    </div>
                </form>
            </section>

            <aside class="editor-sidebar">
                <div class="sidebar-header"><span class="sidebar-title">Tracked Library</span></div>
                <section class="sidebar-section">
                    <div class="section-divider" style="max-height: 600px; overflow-y: auto;">
                        <ul style="list-style:none; padding:0; margin:0;">
                            <?php foreach ($books as $idx => $b): ?>
                                <li style="padding:8px 0; border-bottom:1px solid #eee; display:flex; justify-content:space-between; align-items:center;">
                                    <span style="font-size:0.85em; max-width:80%; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">
                                        <?= e($b['title']) ?>
                                    </span>
                                    <a href="?id=<?= $idx ?>" style="font-size:0.85em;">Edit</a>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </section>
            </aside>
        </div>
    </main>
<?php require __DIR__ . '/../includes/admin-footer.php'; ?>