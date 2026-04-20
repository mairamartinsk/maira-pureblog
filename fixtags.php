<?php
$oldPostsDir = 'microblog/content/';
$newPostsDir = 'content/posts/';

// Get all new PureBlog posts
$newPosts = glob($newPostsDir . '*.md');

foreach ($newPosts as $newPostPath) {
    $newPostContent = file_get_contents($newPostPath);

    // Extract the old post's date and slug from the filename (YYYY-MM-DD-post-slug.md)
    if (preg_match('/^(\d{4}-\d{2}-\d{2})-([^.]+)\.md$/', basename($newPostPath), $matches)) {
        $date = $matches[1];
        $slug = $matches[2];

        // Convert date to old Micro.blog path format (YYYY/MM/DD/slug.md)
        $oldYear = substr($date, 0, 4);
        $oldMonth = substr($date, 5, 2);
        $oldDay = substr($date, 8, 2);
        $oldPostPath = $oldPostsDir . $oldYear . '/' . $oldMonth . '/' . $oldDay . '/' . $slug . '.md';

        // Check if the old post exists
        if (file_exists($oldPostPath)) {
            $oldPostContent = file_get_contents($oldPostPath);

            // Extract categories from old YAML frontmatter
            if (preg_match('/categories:\s*\n(?:- (.+)\n)+/', $oldPostContent, $categoryMatches)) {
                // Extract all categories
                preg_match_all('/- "(.+)"/', $categoryMatches[0], $categories);
                $tags = $categories[1];

                // Update the new post's YAML frontmatter with tags (no quotes)
								$newPostContent = preg_replace(
										'/tags: \[.*\]/',
										'tags: [' . implode(', ', $tags) . ']',
										$newPostContent
								);


                // Save the updated content
                file_put_contents($newPostPath, $newPostContent);
                echo "Updated tags for: " . basename($newPostPath) . "\n";
            }
        }
    }
}

echo "Tag migration complete!\n";
?>
