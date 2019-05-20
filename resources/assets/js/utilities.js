$.extend({
    formatNumberToSpecificDecimalPlaces : function(num,dec_places,ret_empty_str){
        if (typeof dec_places === 'undefined') {
            dec_places = 2;
        }
        
        if (typeof ret_empty_str === 'undefined') {
            ret_empty_str = '';
        }
       
        
        if ( num === null ) {
            return ret_empty_str;
        } else if (  isNaN(num) ) {
            return ret_empty_str;
        } else if (  num === '' ) {
            return ret_empty_str;
        }else {
            num = parseFloat(num);
            num = num.toFixed(dec_places);
            var str = num.toString().split('.');
            var whole = str[0].replace(/(\d)(?=(\d\d\d)+(?!\d))/g, "$1,");
            if ( dec_places > 0 ) {
                var decimal_nums = str[1];
                return whole +'.' +  decimal_nums;
            }else {
                return whole;
            }
        }
    }
});


$.strPad = function(i,l,s) {
    var o = i.toString();
    if (!s) { s = '0'; }
    while (o.length < l) {
        o = s + o;
    }
    return o;
};