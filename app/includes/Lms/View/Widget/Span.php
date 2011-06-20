<?php
/**
 * @copyright 2006-2011 LanMediaService, Ltd.
 * @license    http://www.lanmediaservice.com/license/1_0.txt
 * @author Ilya Spesivtsev <macondos@gmail.com>
 * @version $Id: Span.php 700 2011-06-10 08:40:53Z macondos $
 */

class Lms_View_Widget_Span extends Lms_View_Widget_Abstract
{
    
    public function __construct($attributes = array())
    {
        return parent::__construct('span', $attributes);
    }
}