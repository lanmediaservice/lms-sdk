/**
 * @copyright 2006-2011 LanMediaService, Ltd.
 * @license    http://www.lanmediaservice.com/license/1_0.txt
 * @author Ilya Spesivtsev <macondos@gmail.com>
 * @version $Id: UI.js 700 2011-06-10 08:40:53Z macondos $
 */
 
if (!LMS.Kinopoisk) {
    LMS.Kinopoisk = {};
}

LMS.Kinopoisk.UI = {
    showSearchResults: function (results)
    {
        $('search_results').innerHTML = TEMPLATES.SEARCH_RESULTS.process({results: results});
        $$('.add-form .step-2').invoke('removeClassName', 'hidden');
    },

    showMovieInfo: function (movieInfo)
    {
        $('movie_info').innerHTML = TEMPLATES.MOVIEINFO.process(movieInfo);
        $$('.add-form .step-3').invoke('removeClassName', 'hidden');
    }
};