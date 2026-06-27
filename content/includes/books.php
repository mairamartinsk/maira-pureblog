<?php
/**
 * Public facing single-page bookshelf loader
 * File location: content/includes/books.php
 */
$allBooks = load_books_yaml();
?>

<div class="bookshelf-wrapper">
    <div class="bookshelf-controls" style="margin-bottom: 30px; display: flex; gap: 10px; flex-wrap: wrap;">
        <button class="btn-sort active" onclick="changeSort('chronological', this)">📅 Chronological</button>
        <button class="btn-sort" onclick="changeSort('title', this)">🔤 Sort by Title</button>
        <button class="btn-sort" onclick="changeSort('author', this)">✍️ Sort by Author</button>
        <div style="margin-left: auto; display: flex; gap: 10px;">
            <button class="btn-view active" onclick="changeView('list', this)">📋 Compact List</button>
            <button class="btn-view" onclick="changeView('shelf', this)">📚 Bookshelf Grid</button>
        </div>
    </div>

    <div id="live-bookshelf" class="view-list"></div>
</div>

<style>
/* Clean layouts switching styling hooks */
#live-bookshelf.view-list {
    display: flex;
    flex-direction: column;
    gap: 8px;
}
#live-bookshelf.view-list .book-card {
    display: flex;
    align-items: center;
    padding: 8px 12px;
    background: #fdfdfd;
    border-left: 3px solid #666;
    box-shadow: 0 1px 3px rgba(0,0,0,0.05);
}
#live-bookshelf.view-list img { display: none; }

#live-bookshelf.view-shelf {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(130px, 1fr));
    gap: 20px;
}
#live-bookshelf.view-shelf .book-card {
    display: flex;
    flex-direction: column;
    text-align: center;
}
#live-bookshelf.view-shelf img {
    width: 100%;
    height: 190px;
    object-fit: cover;
    border-radius: 4px;
    box-shadow: 0 4px 6px rgba(0,0,0,0.15);
    margin-bottom: 8px;
    background: #eee;
}
.badge-reread { background: #e3f2fd; color: #0d47a1; font-size: 0.75em; padding: 2px 6px; border-radius: 4px; margin-left: 8px;}
</style>

<script>
// Safely transfer parsed PHP Array straight into Client Side JS
const libraryData = <?php echo json_encode($allBooks, JSON_UNESCAPED_UNICODE); ?>;

let activeSort = 'chronological';
let activeView = 'list';

function renderLibrary() {
    const outputTarget = document.getElementById('live-bookshelf');
    outputTarget.className = `view-${activeView}`;

    // Create sorting mutations copy sequence 
    let processedList = [...libraryData];

    if (activeSort === 'title') {
        processedList.sort((a, b) => a.title.localeCompare(b.title));
    } else if (activeSort === 'author') {
        processedList.sort((a, b) => a.author.localeCompare(b.author));
    } else if (activeSort === 'chronological') {
        // Grab latest year read array index integer or fallback to 0
        processedList.sort((a, b) => {
            const yearA = a.year_read.length ? Math.max(...a.year_read) : 0;
            const yearB = b.year_read.length ? Math.max(...b.year_read) : 0;
            return yearB - yearA; // Latest reading year at top
        });
    }

    outputTarget.innerHTML = processedList.map(book => {
        // Resolve cover photo via free fallback API structure if ISBN provided
        const imgUrl = book.isbn ? `https://covers.openlibrary.org/b/isbn/${book.isbn}-M.jpg` : 'data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" width="100" height="150" viewBox="0 0 100 150"><rect width="100" height="150" fill="%23ddd"/><text x="50" y="75" font-family="sans-serif" font-size="12" fill="%23666" text-anchor="middle">No Cover</text></svg>';
        
        const yearsString = book.year_read.length ? book.year_read.map(y => y === 0 ? 'Before 2015' : y).join(', ') : 'Evergreen';
        
        return `
            <div class="book-card">
                <img src="${imgUrl}" alt="${book.title} Cover" loading="lazy">
                <div class="book-meta">
                    <strong class="book-title">${book.title}</strong> 
                    <span class="book-author">by ${book.author}</span>
                    <small style="color:#777; margin-left:10px;">(${yearsString})</small>
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

// Fire up base application view render
renderLibrary();
</script>