/**
 * @copyright 2006-2011 LanMediaService, Ltd.
 * @license    http://www.lanmediaservice.com/license/1_0.txt
 * @author Ilya Spesivtsev <macondos@gmail.com>
 * @version $Id: Main.js 700 2011-06-10 08:40:53Z macondos $
 */

JSAN.require('LMS.i18n.ru');

LMS.i18n.ru.Main = {
    'Timeout last request': 'Превышен интервал ожидания ответа последнего запроса. Возможно последний запрос не был выполнен.',
    'Loading...': 'Загрузка...',
    'year(s)': "год года лет",
    'month(s)': "месяц месяца месяцев",
    'week(s)': "неделя недели недель",
    'day(s)': "день дня дней",
    'hour(s)': "час часа часов",
    'min(s)': "минуту минуты минут",
    'second(s)': "секунду секунды секунд",
    'ago': "назад",
    'now': "только что",
    'dayNamesShort' : ["вс", "пон", "вт", "ср", "чт", "пт", "сб"],
    'dayNames' : ["воскресенье", "понедельник", "вторник", "среда", "четверг", "пятница", "суббота"],
    'monthNamesShort': ["янв", "фев", "мар", "апр", "май", "июн", "июл", "авг", "сен", "окт", "ноя", "дек"],
    'monthNames': ["января", "февраля", "марта", "апреля", "мая", "июня", "июля", "августа", "сентября", "октября", "ноября", "декабря"],
    'dateformat_full': "d mmmm yyyy г. HH:mm",
    'dateformat_default': "dd.mm.yyyy",
    'dateformat_thisyear': "d mmmm",
    'dateformat_yesterday': "вчера HH:MM",
    'dateformat_today': "HH:MM"
}

LMS.i18n.add(LMS.i18n.ru.Main);