<?php

namespace JekyllImporter;

use JekyllImporter\FileHandler;
use JekyllImporter\FileHandlerException;

class WordpressImporter
{
    /**
     * The path to the WXR filename
     * @var string
     */
    public $WXR = 'WXR-data.xml';

    /**
     * The path to images imported from WP uploads
     * @var string
     */
    public $path_image = '/images';

    /**
     * The path to blog files
     * @var string
     */
    public $path_page = 'blog';

    /**
     * The path to blog posts
     * @var string
     */
    public $path_post = 'blog/_posts';

    /**
     * The path to data for yaml files
     * @var string
     */
    public $path_data = '_data';

    /**
     * The path to the images imported from WP uploads
     * @var string
     */
    public $categ_path = 'blog/category';

    /**
     * The path to the template for pages
     * @var string
     */
    public $template_page = 'templates/template_page.md';

    /**
     * The path to the template for posts
     * @var string
     */
    public $template_post = 'templates/template_post.md';

    /**
     * The path to the template for data category
     * @var string
     */
    public $template_data = 'templates/template_data.yml';

    /**
     * The path to the template for category pages
     * @var string
     */
    public $template_category = 'templates/template_category.md';

    /**
     * The file extension it can be .markdown or .html
     * @var string
     */
    public $output_ext = '.md';

    /**
     * Stores the main XML Object
     * @var object
     */
    private $_data;

    /**
     * File Handler Object
     * @var object
     */
    private $_file;

    /**
     * Object Contructor
     * @var void
     */
    public function __construct($WXR = NULL)
    {
        if ( file_exists($WXR) ) {
            $this->WXR = $WXR;
        }

        if ( !file_exists($this->WXR) ) {
            exit("WXR Not Found \n");
        }

        $xml_data = new \SimpleXMLElement($this->WXR, LIBXML_COMPACT, true);
        $this->_data = $xml_data->channel->item;

        $this->_file = new FileHandler();
    }

    /**
     * Impost Wordpress Posts
     *
     * @return void
     */
    public function importPost()
    {
        foreach ($this->_data as $item) {

            $post_type = (string)$item->children('wp', true)->post_type;

            if ( $post_type === 'post' ) {

                $post_basename = date('Y-m-d', strtotime($item->children('wp', true)->post_date)) . '-' . $item->children('wp', true)->post_name;

                $filename = $this->formatFileName('post', $post_basename);

                $template_post = file_get_contents($this->template_post);

                $post_content = sprintf($template_post,
                        $item->title,
                        $item->category,
                        $item->category->attributes()->nicename,
                        $item->children('wp', true)->post_date,
                        $item->children('wp', true)->post_id,
                        $item->children('wp', true)->post_name,
                        $item->children('content', true)->encoded
                    );

            } elseif ($post_type === 'page') {

                $post_name = $item->children('wp', true)->post_name;
                $filename = $this->formatFileName('page',$post_name);

                $template_page = file_get_contents($this->template_page);

                $post_content = sprintf($template_page,
                        $item->title,
                        $post_name,
                        $item->children('excerpt', true)->encoded,
                        $item->children('content', true)->encoded
                    );
            }

            // Write post files
            try {

                if ( defined('DEBUG') && DEBUG ) {
                    // for debug on browser
                }

                $this->_file->write( $filename, $this->normalizeHTML($post_content) );

            } catch (FileHandlerException $e) {

                $this->_file->setOutput('error', $e->getMessage());
            }
        }
    }

    /**
     * Import Category to a YAML File
     *
     * @date datetimestamp
     * @author Adriano Rosa (http://adrianorosa.com)
     *
     * @param string
     * @return void
     */
    public function importCategory()
    {
        $yaml_format = [];
        $yaml_content = '';

        try {

            if ( !file_exists($this->template_category) ) {
                throw new FileHandlerException(
                    sprintf("Template for category not found --> %s", $this->template_category)
                );

            } else {

                $template_category = file_get_contents($this->template_category);
            }

            foreach ($this->_data as $item) {
                $post_type = (string)$item->children('wp', true)->post_type;

                if ( $post_type !== 'post' ) {
                    continue;
                }

                $yaml_format[(string)$item->category] = (string)$item->category->attributes()->nicename;
            }

            asort($yaml_format);

            $template_data = file_get_contents($this->template_data) . PHP_EOL;

            foreach ($yaml_format as $label => $slug) {

                $yaml_content .= sprintf($template_data, $label, $slug);
                $filename = $this->formatFileName('category', $slug);
                $this->_file->write( $filename, vsprintf($template_category, [$label, $slug]) );
            }

            $this->_file->write($this->path_data . '/categories.yml', trim($yaml_content,"\n\n"));

        } catch (FileHandlerException $e) {

            $this->_file->setOutput('error', $e->getMessage());
        }
    }

    /**
     * Set absolute path
     *
     * @param string $type
     * @param string $basename
     * @return string
     */
    public function formatFileName($type = 'post', $basename)
    {
        switch ($type) {
            case 'category':
                $path = $this->categ_path;
                break;

            case 'page':
                $path = $this->path_page;
                break;

            case 'post':
                $path = $this->path_post;
                break;
        }

        $basename = preg_replace('/\s/', '-', trim($basename));

        return strtolower($path . DIRECTORY_SEPARATOR . $basename . $this->output_ext);
    }

    /**
     * Normalize post content make it compatible with markdown
     *
     * @param string $content The content to be normalized
     * @return string
     */
    public function normalizeHTML($content)
    {
        $normalize_text = array(
            '/<pre.*?>\s{1,}/is'           => '<pre><code>',
            '/\s{1,}<\/pre>/is'            => "</code></pre>", // Normalize code snippet
            '/\n{0,}<ol>|\s{1,}<ol>/is'    => "\n", // Ordered List
            '/\s{1,}<\/ol>|\s{0,}<li>/is'  => '',   // remove extra spaces
            '/<\/li>/is'                   => "\n", // Normalize lists
            '/<strong>/is'                 => "**", // Strong replacement
            '/\s+<\/strong>|<\/strong>/is' => "**", // Close strong
            '/\[caption.*?\]?<a rel.*?>|<\/a>\[\/caption\]/is' => ''
        );

        $content = preg_replace(array_keys($normalize_text), array_values($normalize_text), $content);
        $content = $this->normalizeImages($content);
        return $content;
    }

    /**
     * Normalize images and link tags by removing unecessary attributes
     * converts from Wordpress absolute URL to relative image path
     *
     * @param string $content The content to be normalized
     * @return string
     */
    public function normalizeImages($content)
    {
        $pattern = '/\<a.*?>+<img.*?<\/a>|<img.*?>/msU';
        // $images_normalized = [];

        preg_match_all($pattern, $content, $wp_images);

        if ( ! $wp_images[0] ) {

            return $content;

        } else {

            foreach ($wp_images[0] as $key => $value) {
                $normalized = preg_replace('/(https?:\/\/(.*)\/wp-content\/uploads)/i', $this->path_image, $value);
                $normalized = preg_replace('/(<img.*?)(class="[^"]*"\s)/i', '$1', $normalized);
                $images_normalized[] = $normalized;
            }

            preg_match_all($pattern, $content, $wp_images_to_fix);

            $content = str_replace($wp_images_to_fix[0], $images_normalized, $content);
        }

        return $content;
    }

    /**
     * Execute Importer Class
     *
     * @param array $argv Accept an array of arguments passed via CLI command
     * @return void
     */
    public function run(array $argv = [])
    {
        $count = 0;
        $read_item = 'all';
        unset($argv[0]);

        if ( isset($argv['type'])) {
            if ( $argv['type'] == 'images' ) {
                $read_item = (string)$argv['type'];
            }
        }

        if ( $this->is_cli() ) {

            if ( !isset($argv[1]) ) {

                if ( !function_exists('readline') ) {
                    defined("STDIN") OR define("STDIN", fopen('php://stdin','r'));

                    echo "what kind of data do you want to import? [category, post or all]? ";
                    $read_item = trim(fread(STDIN, 20));

                } else {

                    $read_item = trim(readline("what kind of data do you want to import? [category, post or all]? "));
                }

            } else {

                $read_item = $argv[1];
            }
        }

        switch ($read_item) {
            case 'post':
                $this->importPost();
                break;

            case 'category':
                $this->importCategory();
                break;

            case 'all':
                $this->importCategory();
                $this->importPost();
                break;
        }

        echo $this->_file->getOutput(); //exit();
    }

    private function is_cli()
    {
        return (PHP_SAPI === 'cli' OR defined('STDIN'));
    }
}
