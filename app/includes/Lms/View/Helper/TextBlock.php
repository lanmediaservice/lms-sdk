<?php
/**
 * @copyright 2006-2011 LanMediaService, Ltd.
 * @license    http://www.lanmediaservice.com/license/1_0.txt
 * @author Ilya Spesivtsev <macondos@gmail.com>
 * @version $Id: TextBlock.php 700 2011-06-10 08:40:53Z macondos $
 */

class Lms_View_Helper_TextBlock extends Zend_View_Helper_Abstract
{
    function textBlock($text, $titleThreshold = 0)
    {
        $textBlock = new Lms_View_Widget('div');
        $textBlock->setValue($text);
        if ($titleThreshold && Lms_Text::length($text)>$titleThreshold) {
            $textBlock->setTitle($text);
        }
        return $textBlock;
    }
    
}
