/*
 * jQuery Currency v0.5
 * Simple, unobtrusive currency converting and formatting
 *
 * Copyright 2011, Gilbert Pellegrom
 * Free to use and abuse under the MIT license.
 * http://www.opensource.org/licenses/mit-license.php
 * 
 * http://dev7studios.com
 */

(function($) {

    $.fn.currency = function(method) {

        var methods = {

            init : function(options) {
                var settings = $.extend({}, this.currency.defaults, options);
                return this.each(function() {
                    var $element = $(this),
                         element = this;
                    var value = 0;
                    
                    if($element.is(':input')){
                        value = $element.val();
                    } else {
                        value = $element.text();
                    }
                    
                    if(helpers.isNumber(value)){
                    
                        if(settings.convertFrom != ''){
                            if($element.is(':input')){
                                $element.val(value +' '+ settings.convertLoading);
                            } else {
                                $element.html(value +' '+ settings.convertLoading);
                            }
                            $.post(settings.convertLocation, { amount: value, from: settings.convertFrom, to: settings.region }, function(data){
                                value = data;
                                if($element.is(':input')){
                                    $element.val(helpers.format_currency(value, settings));
                                } else {
                                    $element.html(helpers.format_currency(value, settings));
                                }
                            });
                        } else {
                            if($element.is(':input')){
                                $element.val(helpers.format_currency(value, settings));
                            } else {
                                $element.html(helpers.format_currency(value, settings));
                            }
                        }
                    
                    }
                    
                });

            },

        }

        var helpers = {

            format_currency: function(amount, settings) {
                var bc = settings.region;
                var currency_before = '';
                var currency_after = '';
                
                if(bc == 'BBD') currency_before = '$';
                if(bc == 'USD') currency_before = '$';
                if(bc == 'PRC') currency_after = '%';
                
                if( currency_before == '' && currency_after == '' ) currency_before = '$';
                
                var output = '';
                if(!settings.hidePrefix) output += currency_before;
                output += helpers.number_format( amount, settings.decimals, settings.decimal, settings.thousands );
                if(!settings.hidePostfix) output += currency_after;
                return output;
            },
            
            // Kindly borrowed from http://phpjs.org/functions/number_format
            number_format: function(number, decimals, dec_point, thousands_sep) {
                number = (number + '').replace(/[^0-9+\-Ee.]/g, '');
                var n = !isFinite(+number) ? 0 : +number,
                    prec = !isFinite(+decimals) ? 0 : Math.abs(decimals),
                    sep = (typeof thousands_sep === 'undefined') ? ',' : thousands_sep,
                    dec = (typeof dec_point === 'undefined') ? '.' : dec_point,
                    s = '',
                    toFixedFix = function (n, prec) {
                        var k = Math.pow(10, prec);
                        return '' + Math.round(n * k) / k;
                    };
                // Fix for IE parseFloat(0.55).toFixed(0) = 0;
                s = (prec ? toFixedFix(n, prec) : '' + Math.round(n)).split('.');
                if (s[0].length > 3) {
                    s[0] = s[0].replace(/\B(?=(?:\d{3})+(?!\d))/g, sep);
                }
                if ((s[1] || '').length < prec) {
                    s[1] = s[1] || '';
                    s[1] += new Array(prec - s[1].length + 1).join('0');
                }
                return s.join(dec);
            },
            
            isNumber: function(n) {
                return !isNaN(parseFloat(n)) && isFinite(n);
            }
            
        }

        if (methods[method]) {
            return methods[method].apply(this, Array.prototype.slice.call(arguments, 1));
        } else if (typeof method === 'object' || !method) {
            return methods.init.apply(this, arguments);
        } else {
            $.error( 'Method "' +  method + '" does not exist in currency plugin!');
        }

    }

    $.fn.currency.defaults = {
        region: 'USD', // The 3 digit ISO code you want to display your currency in
        thousands: ',', // Thousands separator
        decimal: '.',   // Decimal separator
        decimals: 2, // How many decimals to show
        hidePrefix: false, // Hide any prefix
        hidePostfix: false, // Hide any postfix
        convertFrom: '', // If converting, the 3 digit ISO code you want to convert from,
        convertLoading: '(Converting...)', // Loading message appended to values while converting
        convertLocation: 'convert.php' // Location of convert.php file
    }

    $.fn.currency.settings = {}

})(jQuery);