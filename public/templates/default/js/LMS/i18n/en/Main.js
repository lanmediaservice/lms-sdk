/**
 * @copyright 2006-2011 LanMediaService, Ltd.
 * @license    http://www.lanmediaservice.com/license/1_0.txt
 * @author Ilya Spesivtsev <macondos@gmail.com>
 * @version $Id: Main.js 700 2011-06-10 08:40:53Z macondos $
 */
 
JSAN.require('LMS.i18n.en');

LMS.i18n.en.Main = {
    'year(s)': "year years years",
    'month(s)': "month months months",
    'week(s)': "week weeks weeks",
    'day(s)': "day days days",
    'hour(s)': "hour hours hours",
    'min(s)': "min mins mins",
    'second(s)': "second seconds seconds",
    'dayNamesShort' : ["Sun", "Mon", "Tue", "Wed", "Thu", "Fri", "Sat"],
    'dayNames' : ["Sunday", "Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday"],
    'monthNamesShort': ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"],
    'monthNames': ["January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"],
    'dateformat_full': "ddd, mmm d, yyyy 'at' h:MM TT",
    'dateformat_default': "m/d/yy",
    'dateformat_thisyear': "mmm d",
    'dateformat_yesterday': "'Yesterday' h:MM TT",
    'dateformat_today': "h:MM TT"
}

LMS.i18n.add(LMS.i18n.en.Main);