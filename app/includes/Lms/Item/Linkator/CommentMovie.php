<?php
/**
 * @copyright 2006-2011 LanMediaService, Ltd.
 * @license    http://www.lanmediaservice.com/license/1_0.txt
 * @author Ilya Spesivtsev <macondos@gmail.com>
 * @version $Id: CommentMovie.php 700 2011-06-10 08:40:53Z macondos $
 */

class Lms_Item_Linkator_CommentMovie extends Lms_Item_Abstract
{
    public function getTableName() 
    {
        return '?_movies_comments';
    }
    
    public function _customInitStructure($struct, $masterDb, $slaveDb)
    {
        parent::_customInitStructure($struct, $masterDb, $slaveDb);
        $struct->addIndex('movie_id', array('movie_id'));
        $struct->addIndex('comment_id', array('comment_id'));
    }

    protected function _preDelete() 
    {
        $this->getChilds('Comment')->delete();
    }
}
