<?php

define('BOOKS_YAML_PATH', PUREBLOG_BASE_PATH . '/data/books.yml');

function load_books_yaml(): array {
    if (!is_file(BOOKS_YAML_PATH)) {
        return [];
    }
    
    if (function_exists('yaml_parse_file')) {
        $nativeItems = yaml_parse_file(BOOKS_YAML_PATH) ?: [];
        foreach ($nativeItems as &$item) {
            if (!isset($item['custom_cover'])) {
                $item['custom_cover'] = '';
            }
        }
        return $nativeItems;
    }
    
    $yaml = file_get_contents(BOOKS_YAML_PATH);
    $blocks = explode("- title:", $yaml);
    array_shift($blocks);
    $items = [];
    
    foreach ($blocks as $block) {
        $lines = explode("\n", $block);
        
        $item = [
            'title'        => '', 
            'author'       => '', 
            'year_read'    => [], 
            'reread'       => false, 
            'olid'         => '', 
            'custom_cover' => '', 
            'tags'         => []
        ];
        
        foreach ($lines as $line) {
            if (preg_match('/^\s*author:\s*(.*)$/', $line, $m)) {
                $item['author'] = trim($m[1], " \t\n\r\0\x0B\"'");
            } elseif (preg_match('/^\s*year_read:\s*\[(.*)\]/', $line, $m)) {
                $item['year_read'] = array_filter(array_map('intval', explode(',', $m[1])), function($v){ return $v !== ''; });
            } elseif (preg_match('/^\s*reread:\s*(true|false)/', $line, $m)) {
                $item['reread'] = $m[1] === 'true';
            } elseif (preg_match('/^\s*olid:\s*(.*)$/', $line, $m)) {
                $item['olid'] = trim($m[1], " \t\n\r\0\x0B\"'");
            } elseif (preg_match('/^\s*custom_cover:\s*(.*)$/', $line, $m)) {
                $item['custom_cover'] = trim($m[1], " \t\n\r\0\x0B\"'");
            } elseif (preg_match('/^\s*tags:\s*\[(.*)\]/', $line, $m)) {
                $item['tags'] = array_filter(array_map(function($t){ return trim($t, " \t\n\r\0\x0B\"'"); }, explode(',', $m[1])));
            }
        }
        $item['title'] = trim($lines[0], " \t\n\r\0\x0B\"'");
        if (!empty($item['title'])) { 
            $items[] = $item; 
        }
    }
    return $items;
}

function save_books_yaml(array $books_array): bool {
    if (function_exists('yaml_emit_file')) {
        $result = yaml_emit_file(BOOKS_YAML_PATH, $books_array);
    } else {
        $output = "";
        foreach ($books_array as $item) {
            $output .= "- title: " . json_encode($item['title'] ?? '', JSON_UNESCAPED_UNICODE) . "\n";
            $output .= "  author: " . json_encode($item['author'] ?? '', JSON_UNESCAPED_UNICODE) . "\n";
            $years = isset($item['year_read']) ? implode(', ', $item['year_read']) : '';
            $output .= "  year_read: [" . $years . "]\n";
            $output .= "  reread: " . (($item['reread'] ?? false) ? 'true' : 'false') . "\n";
            $output .= "  olid: " . json_encode($item['olid'] ?? '', JSON_UNESCAPED_UNICODE) . "\n";
            $output .= "  custom_cover: " . json_encode($item['custom_cover'] ?? '', JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . "\n";
            $escapedTags = array_map(function($t) { return json_encode($t, JSON_UNESCAPED_UNICODE); }, $item['tags'] ?? []);
            $output .= "  tags: [" . implode(', ', $escapedTags) . "]\n\n";
        }
        $result = file_put_contents(BOOKS_YAML_PATH, $output) !== false;
    }

    if (function_exists('cache_clear')) {
        cache_clear();
    }
    return $result;
}