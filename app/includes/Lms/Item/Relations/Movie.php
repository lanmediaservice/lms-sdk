<?php
/**
 * @copyright 2006-2011 LanMediaService, Ltd.
 * @license    http://www.lanmediaservice.com/license/1_0.txt
 * @author Ilya Spesivtsev <macondos@gmail.com>
 * @version $Id: Movie.php 700 2011-06-10 08:40:53Z macondos $
 */

class Lms_Item_Relations_Movie {
    static function perform()
    {
        Lms_Item_Relations::add('Movie', 'Rating', 'movie_id', 'movie_id', Lms_Item_Relations::MANY);
        Lms_Item_Relations::add('Movie', 'Linkator_CommentMovie','movie_id', 'movie_id', Lms_Item_Relations::MANY);
        Lms_Item_Relations::add('Movie', 'Linkator_CountryMovie','movie_id', 'movie_id', Lms_Item_Relations::MANY);
        Lms_Item_Relations::add('Movie', 'Linkator_GenreMovie','movie_id', 'movie_id', Lms_Item_Relations::MANY);
        Lms_Item_Relations::add('Movie', 'Linkator_MoviePersonRole', 'movie_id', 'movie_id', Lms_Item_Relations::MANY);
    }
}
 