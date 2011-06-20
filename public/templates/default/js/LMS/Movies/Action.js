/**
 * @copyright 2006-2011 LanMediaService, Ltd.
 * @license    http://www.lanmediaservice.com/license/1_0.txt
 * @author Ilya Spesivtsev <macondos@gmail.com>
 * @version $Id: Action.js 700 2011-06-10 08:40:53Z macondos $
 */
 
if (!LMS.Movies) {
    LMS.Movies = {};
}

LMS.Movies.Action = {

    getMovie: function (movieId)
    {
        var self = this;
        this.query({
                'action' : 'Movies.getMovie',
                'movie_id' : movieId
            },
            function(result) {
                if (200 == result.status) {
                    self.emit('showMovie', result.response, movieId);
                } else {
                    self.emit('userError', result.status, result.message);
                }
            }
        );
    },

    getPerson: function (movieId, personId)
    {
        var self = this;
        this.query({
                'action' : 'Movies.getPerson',
                'person_id' : personId
            },
            function(result) {
                if (200 == result.status) {
                    self.emit('showPerson', result.response, movieId, personId);
                } else {
                    self.emit('userError', result.status, result.message);
                }
            }
        );
    }

};