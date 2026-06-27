<?php
declare(strict_types=1);

require_once __DIR__ . './../../functions.php';

require_setup_redirect();

$config = load_config();
$fontStack = font_stack_css($config['theme']['font_stack'] ?? 'sans');
$pageTitle = 'Books';
$metaDescription = '';

require __DIR__ . './../../includes/header.php';
render_masthead_layout($config, ['page' => $page ?? null]);

$allBooks = function_exists('load_books_yaml') ? load_books_yaml() : [];
?>

<main>
	<div class="bookshelf-views">
            <button class="btn-view active" onclick="changeView('list', this)">List View</button>
            <button class="btn-view" onclick="changeView('shelf', this)">Cover View</button>
        </div>
	<h1>Books</h1>
	<p>A list of all books I've ever read. Probably.</p>
<div class="bookshelf-wrapper">
    <div class="bookshelf-controls">
        <button class="btn-sort active" onclick="changeSort('chronological', this)">Sort by Year</button>
        <button class="btn-sort" onclick="changeSort('title', this)">Sort by Title</button>
        <button class="btn-sort" onclick="changeSort('author', this)">Sort by Author</button>
    </div>

    <div id="live-bookshelf" class="view-list"></div>
</div>

<script>
const libraryData = <?php echo json_encode($allBooks, JSON_UNESCAPED_UNICODE); ?>;

let activeSort = 'chronological';
let activeView = 'list';

function renderLibrary() {
    const outputTarget = document.getElementById('live-bookshelf');
    outputTarget.className = `view-${activeView}`;

    let processedList = [...libraryData];

    if (activeSort === 'title') {
        processedList.sort((a, b) => (a.title || '').localeCompare(b.title || ''));
    } else if (activeSort === 'author') {
        processedList.sort((a, b) => (a.author || '').localeCompare(b.author || ''));
    } else if (activeSort === 'chronological') {
        processedList.sort((a, b) => {
            const yearA = (a.year_read && a.year_read.length) ? Math.max(...a.year_read) : 0;
            const yearB = (b.year_read && b.year_read.length) ? Math.max(...b.year_read) : 0;
            return yearB - yearA;
        });
    }

    outputTarget.innerHTML = processedList.map(book => {
        // Resolve cover photo via free OpenLibrary
        const imgUrl = book.isbn ? `https://covers.openlibrary.org/b/isbn/${book.isbn}-M.jpg` : '';
        
        const yearsString = (book.year_read && book.year_read.length) 
            ? book.year_read.map(y => y === 0 ? 'Before 2015' : y).join(', ') 
            : '';
            
        const tagsString = (book.tags && book.tags.length)
            ? `<span class="book-tags">${book.tags.join(', ')}</span>`
            : '';
        
        return `
            <div class="book-card ${book.reread ? 'is-reread' : ''}">
                ${imgUrl ? `<img src="${imgUrl}" alt="${book.title} Cover" class="book-cover" loading="lazy">` : '<div class="book-cover placeholder"></div>'}
                <div class="book-meta">
                    <strong class="book-title">${book.title}</strong> 
                    <span class="book-author">by ${book.author}</span>
                    <span class="book-time">(${yearsString})</span>
                    ${tagsString}
                    ${book.reread ? '<span class="badge-reread">🔄 Re-read</span>' : ''}
                </div>
            </div>
        `;
    }).join('');
}

function changeSort(sortMode, element) {
    document.querySelectorAll('.btn-sort').forEach(el => el.classList.remove('active'));
    element.classList.add('active');
    activeSort = sortMode;
    renderLibrary();
}

function changeView(viewMode, element) {
    document.querySelectorAll('.btn-view').forEach(el => el.classList.remove('active'));
    element.classList.add('active');
    activeView = viewMode;
    renderLibrary();
}

renderLibrary();
</script>