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
		<button class="btn-reset" onclick="resetAllFilters()">Reset</button>
	</div>

	<h1>Books</h1>
	<p>A list of all the books I've ever read, maybe. Anything before 2015 is bundled together.</p>

	<div class="bookshelf-wrapper">
		<div class="bookshelf-controls">
			<button class="btn-sort active" onclick="changeSort('date', this)">Sort by Year</button>
			<button class="btn-sort" onclick="changeSort('title', this)">Sort by Title</button>
			<button class="btn-sort" onclick="changeSort('author', this)">Sort by Author</button>

			<div class="drop-sort">
				<select class="bookshelf-filter-select" onchange="changeFilter(this.value)">
					<option value="">More:</option>
					<?php
						$allTags = [];
						foreach ($allBooks as $b) {
						if (!empty($b['tags']) && is_array($b['tags'])) {
								foreach ($b['tags'] as $t) {
									$allTags[] = trim($t);
								}
							}
						}
						$uniqueTags = array_unique($allTags);
						sort($uniqueTags);
						foreach ($uniqueTags as $tag):
					?>
					<option value="<?= e($tag) ?>"><?= e($tag) ?>
					</option>
					<?php endforeach; ?>
				</select>
			</div>
		</div>

		<div id="live-bookshelf" class="view-list"></div>

	</div>
</main>

<?php render_footer_layout($config, ['page' => $page ?? null]); ?>

</body>
</html>

<script>
	const libraryData = <?php echo json_encode($allBooks, JSON_UNESCAPED_UNICODE); ?>;

	let activeSort = 'date';
	let activeView = 'list';
	let activeFilterTag = '';

	function renderLibrary() {
		const outputTarget = document.getElementById('live-bookshelf');
		outputTarget.className = `view-${activeView}`;

		let processedList = [...libraryData];
		if (activeFilterTag !== '') {
			processedList = processedList.filter(book =>
				book.tags && book.tags.includes(activeFilterTag)
			);
		}

		if (activeSort === 'title') {
			processedList.sort((a, b) => (a.title || '').localeCompare(b.title || ''));
		} else if (activeSort === 'author') {
			processedList.sort((a, b) => (a.author || '').localeCompare(b.author || ''));
		} else if (activeSort === 'date') {
			processedList.sort((a, b) => {
				const yearA = (a.year_read && a.year_read.length) ? Math.max(...a.year_read) : 0;
				const yearB = (b.year_read && b.year_read.length) ? Math.max(...b.year_read) : 0;
				return yearB - yearA;
			});
		}

		let currentYearHeading = null;
		let htmlOutput = '';

		processedList.forEach(book => {
			const maxYear = (book.year_read && book.year_read.length) ? Math.max(...book.year_read) : 0;
			const displayYearStr = maxYear === 0 ? 'Before 2015' : maxYear.toString();

			const yearsString = (book.year_read && book.year_read.length)
				? book.year_read.map(y => y === 0 ? 'Before 2015' : y).join(', ')
				: '';

			const tagsString = (book.tags && book.tags.length)
				? `<span class="book-tags">${book.tags.join(', ')}</span>`
				: '';

			// Render List View
			if (activeView === 'list') {
				if (activeSort === 'date' && currentYearHeading !== displayYearStr) {
					currentYearHeading = displayYearStr;
					htmlOutput += `<h2 class="bookshelf-year-heading">${currentYearHeading}</h2>`;
				}

				htmlOutput += `
                <div class="book-card ${book.reread ? 'is-reread' : ''}">
                    <div class="book-meta">
                        <strong class="book-title">${book.title}</strong> 
                        <span class="book-author">by ${book.author}</span>
                        ${book.reread ? '<span class="badge-reread">re-read</span>' : ''}
                    </div>
                </div>
            `;
			} else {
				// Render Cover View
				if (book.isbn) {
					const imgUrl = `https://covers.openlibrary.org/b/isbn/${book.isbn}-M.jpg`;
					htmlOutput += `
                    <div class="book-card ${book.reread ? 'is-reread' : ''}">
                        <img src="${imgUrl}" alt="${book.title} Cover" class="book-cover" loading="lazy">
                    </div>
                `;
				} else {
					htmlOutput += `
                    <div class="book-card missing-cover ${book.reread ? 'is-reread' : ''}">
                        <div class="placeholder-cover">
                            <span class="placeholder-title">${book.title}</span>
                        </div>
                    </div>
                `;
				}
			}
		});

		outputTarget.innerHTML = htmlOutput;
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

	function changeFilter(selectedTag) {
		activeFilterTag = selectedTag;
		const selectMenu = document.querySelector('.bookshelf-filter-select');
		if (selectMenu) {
			selectMenu.value = selectedTag;
		}
		renderLibrary();
	}

	function resetAllFilters() {
		activeFilterTag = '';
		activeSort = 'date';

		const selectMenu = document.querySelector('.bookshelf-filter-select');
		if (selectMenu) {
			selectMenu.value = '';
		}

		document.querySelectorAll('.btn-sort').forEach(el => {
			el.classList.remove('active');
			if (el.textContent.trim() === 'Sort by Year') {
				el.classList.add('active');
			}
		});

		renderLibrary();
	}
</script>