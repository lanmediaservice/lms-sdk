/**
 * @copyright 2006-2011 LanMediaService, Ltd.
 * @license    http://www.lanmediaservice.com/license/1_0.txt
 * @author Ilya Spesivtsev <macondos@gmail.com>
 * @version $Id: Action.js 700 2011-06-10 08:40:53Z macondos $
 */
 
if (!LMS.Kinopoisk) {
    LMS.Kinopoisk = {};
}

LMS.Kinopoisk.Action = {

    searchMovie: function ()
    {
        var queryText = $('query_text').value;
        if (!queryText) {
            this.emit('highlightElement', 'query_text');
            return;
        }
        var self = this;
        this.query({
                'action' : 'Kinopoisk.searchMovie',
                'query' : queryText
            },
            function(result) {
                if (200 == result.status) {
                    self.emit('postSearch', result.response);
                } else {
                    self.emit('userError', result.status, result.message);
                }
            }
        );
        return false;
    },

    parseMovie: function (kinopoiskId)
    {
        var self = this;
        this.query({
                'action' : 'Kinopoisk.parseMovie',
                'kinopoisk_id' : kinopoiskId
            },
            function(result) {
                if (200 == result.status) {
                    self.emit('postParseMovie', result.response);
                } else {
                    self.emit('userError', result.status, result.message);
                }
            }
        );
    },

    setKinopoiskId: function (movieInfo)
    {
        $('kinopoisk_id').value = movieInfo.kinopoisk_id;
    }
};