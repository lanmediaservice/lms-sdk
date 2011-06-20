/**
 * @copyright 2006-2011 LanMediaService, Ltd.
 * @license    http://www.lanmediaservice.com/license/1_0.txt
 * @author Ilya Spesivtsev <macondos@gmail.com>
 * @version $Id: Text.js 700 2011-06-10 08:40:53Z macondos $
 */
 
LMS.Text = {}
        
LMS.Text.declension = function(int, expressions, langCode2)
{
    if (!langCode2) {
        langCode2 = 'en';
    }
    if (expressions.length < 2) {
        expressions[1] = expressions[0];
    }
    if (expressions.length < 3) {
        expressions[2] = expressions[1];
    }
    var result;
    switch (langCode2) {
        case 'en':
            result = int==1? expressions[0] : expressions[1];
            break;
        case 'ru':
        case 'uk':
        case 'be':
            var count = int % 100; 
            if (count >= 5 && count <= 20) { 
                result = expressions[2];
            } else { 
                count = count % 10; 
                if (count == 1) { 
                    result = expressions[0]; 
                } else if (count >= 2 && count <= 4) {
                    result = expressions[1];
                } else { 
                    result = expressions[2];
                }
            } 
            break;
        default: 
            result = expressions[0];
    }
    return result; 
}