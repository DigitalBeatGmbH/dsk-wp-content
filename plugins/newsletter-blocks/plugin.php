<?php

class NewsletterBlocks extends NewsletterAddon {

    /**
     * @var NewsletterBlocks
     */
    static $instance;

    function __construct($version) {
        self::$instance = $this;
        parent::__construct('blocks', $version);

        add_filter('newsletter_blocks_dir', array($this, 'hook_newsletter_blocks_dir'));
    }

    function hook_newsletter_blocks_dir($blocks_dir) {
        $blocks_dir[] = __DIR__ . '/blocks';
        
        return $blocks_dir;
    }

    function scan($dir) {
        if (!is_dir($dir)) {
            return false;
        }

        $handle = opendir($dir);
        $list = array();
        $relative_dir = substr($dir, strlen(WP_CONTENT_DIR));
        while ($file = readdir($handle)) {

            $full_file = $dir . '/' . $file . '/block.php';
            if (!is_file($full_file)) {
                continue;
            }

            $data = get_file_data($full_file, array('name' => 'Name', 'section' => 'Section', 'description' => 'Description'));
            $data['id'] = $file;
            if (empty($data['name'])) {
                $data['name'] = $file;
            }
            if (empty($data['section'])) {
                $data['section'] = 'content';
            }
            if (empty($data['description'])) {
                $data['description'] = '';
            }

            $data['icon'] = content_url($relative_dir . '/' . $file . '/icon.png');
            $list[$file] = $data;
        }
        closedir($handle);
        return $list;
    }

}

