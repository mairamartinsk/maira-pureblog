<?php
$postTarget = './content/posts';

foreach (glob("$postTarget/*.md") as $file) {
    $content = file_get_contents($file);

    // 1. Get the post title from frontmatter to use as fallback alt text
    preg_match('/title:\s*"(.*?)"/', $content, $titleMatch);
    $fallbackAlt = $titleMatch[1] ?? 'image';

    // 2. Find all <img> tags
    // This regex captures the entire tag and uses lookaheads to find src and alt
    $content = preg_replace_callback('/<img\s+[^>]*?src=["\']([^"\']+)["\'][^>]*>/i', function($matches) use ($fallbackAlt, $content) {
        $fullTag = $matches[0];
        $src = $matches[1];

        // Try to extract alt attribute from the current <img> tag
        if (preg_match('/alt=["\']([^"\']*)["\']/', $fullTag, $altMatch) && !empty(trim($altMatch[1]))) {
            $alt = trim($altMatch[1]);
        } else {
            $alt = $fallbackAlt;
        }

        // Return the Markdown equivalent
        return "![$alt]($src)";
    }, $content);

    file_put_contents($file, $content);
    echo "Converted images in: " . basename($file) . "\n";
}

echo "\nConversion complete.";