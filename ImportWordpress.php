#!/usr/bin/php
<?php
if(!defined("STDIN")) {
    define('DEBUG', false);
}

class ImportWordpress
{

    public $_WXR = 'WXR-posts.xml';

    public $post_path = 'blog/_posts';

    public $data_path = '_data';

    public $categ_path = 'blog/category';

    public $template_post = "---\ntitle: %s\ncategory_label: %s\ncategory: %s\ndate: %s\nwp_id: %s\nwp_slug: %s\n---\n\n%s";

    public $template_yaml = "- name: %s\n  slug: %s\n\n";

    public $template_category = 'template_category.md';

    // public $category_content;

    public $output_ext = '.md';

    private $_data;

    private $_result = array();

    public function __construct($WXR = NULL)
    {
        if ( file_exists($WXR) ) {
            $this->$_WXR = $WXR;
        }

        if ( !file_exists($this->_WXR) ) {
            exit("WXR Not Found \n");
        }

        $xml_data = new SimpleXMLElement($this->_WXR, LIBXML_COMPACT, true);
        $this->_data = $xml_data->channel->item;
    }

    /**
     * Impost Post
     *
     * @date datetimestamp
     * @author Adriano Rosa (http://adrianorosa.com)
     *
     * @param string
     * @return void
     */
    public function importPost()
    {
        foreach ($this->_data as $item) {

            $post_name = $this->setPostName($item->children('wp', true)->post_date, $item->children('wp', true)->post_name);

            $post_content = sprintf($this->template_post,
                    $item->title,
                    $item->category,
                    $item->category->attributes()->nicename,
                    $item->children('wp', true)->post_date,
                    $item->children('wp', true)->post_id,
                    $item->children('wp', true)->post_name,
                    $item->children('content', true)->encoded
                );

            if ( defined('DEBUG') && DEBUG ) {
                // if ( $item->category->attributes()->nicename == 'tecnologia') {
                    _pr( htmlentities( $this->normalizeHTML($post_content) ));
                // }

            } else {
                $post_content = $this->normalizeHTML($post_content);
            }

            $this->writeFile($post_name, $post_content);
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
        $yaml_format = array();
        $yaml_content = '';

        if ( !file_exists($this->template_category) ) {

            $this->setVerbose('error', sprintf("Template for category not found --> %s", $this->template_category));

        } else {

            $template_category = file_get_contents($this->template_category);
        }

        foreach ($this->_data as $item) {
            $yaml_format[(string)$item->category] = (string)$item->category->attributes()->nicename;
        }

        asort($yaml_format);

        foreach ($yaml_format as $label => $slug) {

            $yaml_content .= sprintf($this->template_yaml, $label, $slug);

            $this->writeFile($this->categ_path . '/' . $slug . $this->output_ext, vsprintf($template_category, array($label, $slug)));
        }

        $this->writeFile($this->data_path . '/categories.yml', trim($yaml_content,"\n\n"));
    }

    /**
     * Write Files
     *
     * @date datetimestamp
     * @author Adriano Rosa (http://adrianorosa.com)
     *
     * @param string
     * @return void
     */
    public function writeFile($filename, $content, $type = null)
    {
        if ( !file_exists($filename) ) {

            $message = "Created and Imported data to file --> %s";

        } else {

            $message = "Updated data to file --> %s";
        }

        if ( ! $handle = @fopen($filename, 'w')) {
            $this->setVerbose('error', sprintf("Unable to read a file --> %s", $filename));
            return;
        }

        flock($handle, LOCK_EX);

        if ( fwrite($handle, $content) === FALSE ) {
            $this->setVerbose('error', sprintf("Unable to create a file --> %s", $filename));
            return;
        }

        $this->setVerbose('success', sprintf($message, $filename));

        flock($handle, LOCK_UN);
        fclose($handle);

        return $this;

            // $file = new SplFileObject($filename, "w");

            // if ( $file->isWritable() ) {

            //     $file->fwrite($content);
            //     // chmod($filename, 0660);
            //     $this->setVerbose('success', 'Imported: ' . $filename);
            // } else {

            //     $this->setVerbose('error', 'Permission denied: ' . $filename);
            // }

            // $file = null;
        // }

        // return $this->_result;
    }

    public function setPostName($date_string, $string_name, $format_slug = false)
    {
        if ( is_numeric($date_string) ) {
            $date_string = $date_string;
        } else {
            $date_string = strtotime($date_string);
        }

        if ( $format_slug === TRUE ) {
            $string_name = preg_replace('/\s/', '-', trim($string_name));
        }

        return strtolower($this->post_path . '/' . date('Y-m-d', $date_string) . '-' . $string_name . $this->output_ext);
    }

    public function normalizeHTML($string)
    {
        $normalize_text = array(
            '/<pre.*?>\s{1,}/is'           => '<pre><code>',
            '/\s{1,}<\/pre>/is'            => "</code></pre>", // Normalize code snippet
            '/\n{0,}<ol>|\s{1,}<ol>/is'    => "\n", // Ordered List
            '/\s{1,}<\/ol>|\s{0,}<li>/is'  => '',
            '/<\/li>/is'                   => "\n",
            '/<strong>/is'                 => "**", // Strong replacement
            '/\s+<\/strong>|<\/strong>/is' => "**", // Close strong
            '/\[caption.*?\]?<a rel.*?>|<\/a>\[\/caption\]/is' => '',
        );

        $string = preg_replace(array_keys($normalize_text), array_values($normalize_text), $string);

        return $string;
    }

    public function run(array $argv)
    {
        $count = 0;
        $read_item = 'all';

        if ( $this->is_cli() ) {

            if(!defined("STDIN")) {
                // define("STDIN", fopen('php://stdin','r'));
                exit('STDIN NOT DEFINED!');
            }

            if ( !isset($argv[1]) ) {
                echo "type what you want to import? [category, post or all]? ";
                $read_item = trim(fread(STDIN, 20));

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

        foreach ($this->_result as $result) {

            if ( isset($result['success']) ) {

                $count++;
                echo $result['success'];

            } elseif ( isset($result['error']) ) {

                $count --;
                echo $result['error'];
            }

        }
        echo "------------------------------". PHP_EOL;
        echo ($count < 0) ? abs($count) . ' Errors found' : $count . ' Itens Was Imported';
        echo PHP_EOL;
        echo "------------------------------". PHP_EOL;
        exit();
    }

    public function is_cli()
    {

        return (PHP_SAPI === 'cli' OR defined('STDIN'));
    }

    public function setVerbose($type = 'error', $string = null)
    {
        if ( $type === 'success' ) {

            $message = sprintf("✔ Success %s", $string);

        } else {

            $message = sprintf("✗ Error %s", $string);
        }

        if ( $this->is_cli() ) {

            $pretty = ($type === 'success')
                ? "\033[0;32m"
                : "\033[0;31m";

            $message = $pretty . $message . "\033[0m" . PHP_EOL;

        } else {

            $message .= "<br>" . PHP_EOL;
        }

        $this->_result[][$type] = $message;
        return $this;
    }
}
    if ( !isset($argv) ) $argv = array();
    $import = new ImportWordpress();
    $import->run($argv);


function _vd($str, $exit = false)
{
    if (  !$exit ) {
        var_dump($str); exit();
    }
    // $memory = round(memory_get_usage() / 1024 / 1024, 2).'MB';
    var_dump($str);

    // echo "<hr>$memory"; exit();
}

function _pr ($str)
{
    echo "<pre>";
    echo print_r($str); exit();
}
