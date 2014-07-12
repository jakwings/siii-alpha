<?php
class Siii
{
    private $mDirRoot = NULL;
    private $mDirCache = 'cache';
    private $mDirConfig = 'config';
    private $mDirArticles = 'files';
    private $mDirTemplates = 'templates';
    private $mMetadata = NULL;
    private $mTimelines = NULL;
    private $mIsTimelinesOK = FALSE;
    private $mCacheFile = NULL;
    private $mSlug = '';

    public function __construct()
    {
        $this->mDirRoot = rtrim(realpath(dirname(__FILE__) . '/../'), '/') . '/';
        $this->mDirCache = $this->_GetRealDir($this->mDirCache);
        $this->mDirConfig = $this->_GetRealDir($this->mDirConfig);
        $this->mDirArticles = $this->_GetRealDir($this->mDirArticles);
        $this->mDirTemplates = $this->_GetRealDir($this->mDirTemplates);
        $this->mSlug = strval($_GET['view']);

        mb_language('uni');
        mb_internal_encoding('UTF-8');
        mb_regex_encoding('UTF-8');
        mb_regex_set_options('pz');

        require_once 'class-toml.php';
        $config = \Toml\Toml::parseFile($this->mDirConfig . 'config.toml');
        $this->mTimelines = $config['timelines'] ?: array();
        $this->mMetadata = $config['metadata'];
        date_default_timezone_set($this->mMetadata['timezone']);

        $cache_id = md5($this->slug());
        $this->mCacheFile = $this->GetSecurePath($this->mDirCache . $cache_id);
        $this->_CheckHtaccess();
        $this->_UpdateFeed();
    }

    public function metadata($key, $escape = FALSE)
    {
        $metadata = $this->mMetadata;
        $key = strval($key);
        if ($key !== '') {
            $val = $metadata[$key];
            return $escape ? $this->EscapeHtml(strval($val)) : $val;
        }
        return $metadata;
    }

    public function timelines()
    {
        if (!$this->mIsTimelinesOK) {
            $this->_SetupTimelines();
        }
        return $this->mTimelines;
    }

    public function slug()
    {
        return $this->mSlug;
    }

    public function Load($template, $data = NULL)
    {
        include $this->GetSecurePath($this->mDirTemplates . $template . '.php');
    }

    public function ReadArticle($slug)
    {
        $path = $this->GetSecurePath($this->mDirArticles . $slug . '.md');
        $content = file_get_contents($path);
        return $this->ParseMarkdown($content);
    }

    public function ParseMarkdown($source)
    {
        require_once 'class-parsedown.php';
        $parser = new Parsedown();
        return $parser->text($source);
    }

    public function GetSecurePath($path)
    {
        if (preg_match('/\/\?\.\?\./', '/' . $path)) {
            throw new Exception('Invalid path.');
        }
        return $path;
    }

    public function EscapeHtml($text)
    {
        return htmlentities($text, ENT_QUOTES, 'UTF-8');
    }

    public function _SetupTimelines()
    {
        if ($this->mIsTimelinesOK) {
            return;
        }
        $timelines = $this->mTimelines;
        $dir_config = $this->mDirConfig;
        $timelines = array_filter($timelines, function ($timeline) use ($dir_config) {
            return !empty($timeline['name'])
                    and file_exists($dir_config . $timeline['file']);
        });
        foreach ($timelines as $key => $timeline) {
            $events = array();
            $lines = file($this->mDirConfig . $timeline['file'],
                          FILE_IGNORE_NEW_LINES);
            $lines[] = '%';
            $event = array('content' => array());
            $is_header = TRUE;
            foreach ($lines as $line) {
                if ($line === '%') {
                    $is_header = TRUE;
                    $event['content'] = implode("\n", $event['content']);
                    $events[] = $event;
                    $event = array('content' => array());
                    continue;
                }
                if ($is_header) {
                    $event['headers'] = mb_split('\s*//\s*', trim($line));
                    $event['date'] = strtotime($event['headers'][0] ?: NULL);
                    $event['category'] = strval($event['headers'][1]);
                    $event['slug'] = strval($event['headers'][2]);
                    $is_header = FALSE;
                } else {
                    $event['content'][] = $line;
                }
            }
            $events = array_filter($events, function ($event) {
                return !empty($event['headers']) and $event['category'] !== '';
            });
            usort($events, function ($a, $b) {
                return $a['date'] <= $b['date'];  // use `<=` if ...
            });
            $timelines[$key]['events'] = $events;
        }
        $this->mTimelines = $timelines;
        $this->mIsTimelinesOK = TRUE;
    }

    public function FindCache()
    {
        $cmd_file = $this->mDirRoot . '.clear_cache';
        if (file_exists($cmd_file)) {
            $filenames = glob($this->mDirCache . '*', GLOB_NOSORT);
            ignore_user_abort(TRUE);
            foreach ($filenames as $filename) {
                unlink($filename);
            }
            unlink($cmd_file);
            $this->_UpdateFeed(TRUE);
            ignore_user_abort(FALSE);
        } else {
            if (file_exists($this->mCacheFile)) {
                readfile($this->mCacheFile);
                exit();
            }
        }
        $this->_StartCache();
    }

    private function _StartCache()
    {
        ob_clean();
        if (version_compare(PHP_VERSION, '5.4.0', '>=')) {
            ob_start(array($this, '_EndCache'), 0, PHP_OUTPUT_HANDLER_FLUSHABLE);
        } else {
            ob_start(array($this, '_EndCache'), 0, false);
        }
    }
    private function _EndCache($buffer, $phase)
    {
        if ($phase & PHP_OUTPUT_HANDLER_END) {
            ignore_user_abort(TRUE);
            file_put_contents($this->mCacheFile, $buffer, LOCK_EX);
            ignore_user_abort(FALSE);
        }
        return $buffer;
    }

    private function _GetRealDir($dir)
    {
        $root = $this->mDirRoot;
        return rtrim(realpath($root . $dir . '/') ?: '/dev/null', '/') . '/';
    }

    private function _CheckHtaccess()
    {
        if (!file_exists('./.htaccess')) {
            require 'htaccess.php';
        }
    }

    private function _UpdateFeed($force = FALSE)
    {
        $feedfile = $this->mDirRoot . 'feed.xml';
        if (!$force and file_exists($feedfile)) {
            return;
        }
        $homepage = rtrim($this->metadata('homepage'), '/') . '/';
        include_once 'class-feed.php';
        $feed = new RssFeed($this->metadata('title'),
                            $homepage,
                            $this->metadata('subtitle'));
        $feed->Config('ttl', '90');
        $feed->Config('category', 'blog');
        $license = $this->metadata('license');
        $feed->Config('copyright', $license['text']);

        $events = array();
        foreach ($this->timelines() as $timeline) {
            if (!$timeline['feed']) {
                continue;
            }
            foreach ($timeline['events'] as $event) {
                if ($event['slug'] === '') {
                    continue;
                }
                $events[] = $event;
            }
        }
        usort($events, function ($a, $b) {
            return $a['date'] <= $b['date'];  // use `<=` if ...
        });
        foreach ($events as $item) {
            $lines = explode("\n", $item['content']);
            $feed->AddItem(array(
                'title' => $lines[0],
                'link' => $homepage . $item['slug'] . '.html',
                'guid' => $homepage . $item['slug'] . '.html',
                'pubDate' => $item['date'],
                'description' => $item['content'],
                'category' => $item['category']
            ));
        }
        ignore_user_abort(TRUE);
        file_put_contents($feedfile, $feed->Fetch(), LOCK_EX);
        ignore_user_abort(FALSE);
    }
}
?>
