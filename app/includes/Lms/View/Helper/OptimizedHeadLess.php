<?php
/**
 * @copyright 2006-2011 LanMediaService, Ltd.
 * @license    http://www.lanmediaservice.com/license/1_0.txt
 * @author Ilya Spesivtsev <macondos@gmail.com>
 * @version $Id: OptimizedHeadLess.php 700 2011-06-10 08:40:53Z macondos $
 */

class Lms_View_Helper_OptimizedHeadLess extends Lms_View_Helper_HeadLess
{
    private static $cacheDir;
    
    private $_cache = array();
    
    static public function setCacheDir($dir)
    {
        self::$cacheDir = rtrim($dir, '/') . '/';
    }
    
    public function optimizedHeadLess()
    {
        if (Lms_Application::getConfig('optimize', 'less_combine')) {
            return $this->toString();
        } else {
            return $this->view->headLess();
        }
    }
    
    public function itemToString(stdClass $item)
    {
        $attributes = (array) $item;
        $link       = '<link ';

        foreach ($this->_itemKeys as $itemKey) {
            if (isset($attributes[$itemKey])) {
                if(is_array($attributes[$itemKey])) {
                    foreach($attributes[$itemKey] as $key => $value) {
                        $link .= sprintf('%s="%s" ', $key, ($this->_autoEscape) ? $this->_escape($value) : $value);
                    }
                } else {
                    $link .= sprintf('%s="%s" ', $itemKey, ($this->_autoEscape) ? $this->_escape($attributes[$itemKey]) : $attributes[$itemKey]);
                }
            }
        }

        if ($this->view instanceof Zend_View_Abstract) {
            $link .= ($this->view->doctype()->isXhtml()) ? '/>' : '>';
        } else {
            $link .= '/>';
        }

        if (($link == '<link />') || ($link == '<link >')) {
            return '';
        }

        if (isset($attributes['conditionalStylesheet'])
            && !empty($attributes['conditionalStylesheet'])
            && is_string($attributes['conditionalStylesheet']))
        {
            $link = '<!--[if ' . $attributes['conditionalStylesheet'] . ']> ' . $link . '<![endif]-->';
        }

        return $link;
    }

    public function isCachable($item)
    {
        $attributes = (array) $item;
        if (isset($attributes['conditionalStylesheet'])
            && !empty($attributes['conditionalStylesheet'])
            && is_string($attributes['conditionalStylesheet']))
        {
            return false;
        }
        if (!isset($attributes['href']) || !is_readable($_SERVER['DOCUMENT_ROOT'] . $attributes['href'])) {
            return false;
        }
        return true;
    }
    
    public function cache($item)
    {
        $attributes = (array) $item;
        $filePath = $_SERVER['DOCUMENT_ROOT'] . $attributes['href'];
        $this->_cache[] = array(
            'filepath' => $filePath,
            'mtime' => filemtime($filePath)
        );
        
    }
    
    public function toString($indent = null)
    {
        $headLess = $this->view->headLess();
        
        $indent = (null !== $indent)
                ? $headLess->getWhitespace($indent)
                : $headLess->getIndent();

        $items = array();
        $headLess->getContainer()->ksort();
        foreach ($headLess as $item) {
            if (!$headLess->_isValid($item)) {
                continue;
            }
            if (!$this->isCachable($item)) {
                $items[] = $this->itemToString($item);
            } else {
                $this->cache($item);
            }
        }
        
        array_unshift($items, $this->itemToString($this->getCompiledItem()));
        //Lms_Debug::debug(print_r($this->_cache, 1));

        $return = implode($headLess->getSeparator(), $items);
        return $return;
    }
    
    private function getCompiledItem()
    {
        $filename = md5(serialize($this->_cache));
        $path = self::$cacheDir . $filename . '.less';
        if (!file_exists($path)) {
            Lms_Debug::debug("Combine less to $path...");
            Lms_FileSystem::createFolder(dirname($path), 0777, true);
            $lessContent = '';
            foreach ($this->_cache as $less) {
                $content = file_get_contents($less['filepath']);
                $lessContent .= Minify_CSS_UriRewriter::rewrite(
                    $content, 
                    dirname($less['filepath']),
                    $_SERVER['DOCUMENT_ROOT'],
                    Lms_Application::getConfig('symlinks')
                );
                $lessContent .= "\n\n";
                Lms_Debug::debug($less['filepath'] . ' ... OK');
            }
            file_put_contents($path, $lessContent);

        }
        
        $url = str_replace($_SERVER['DOCUMENT_ROOT'], '', $path);
        $item = $this->createDataStylesheet(array('href'=>$url));
        return $item;
    }
}
