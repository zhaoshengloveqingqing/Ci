/*!
 * jQuery namespaced 'Starter' plugin boilerplate
 * Author: @dougneiner
 * Further changes: @addyosmani
 * Licensed under the MIT license
 */

;(function ( $ ) {
    if (!$.pinet) {
        $.pinet = {};
    };

    $.pinet.cascadeSelect = function ( el, myFunctionParam, options ) {
        // To avoid scope issues, use 'base' instead of 'this'
        // to reference this class from internal events and functions.
        var base = this;

        // Access to jQuery and DOM versions of element
        base.$el = $(el);
        base.el = el;

        // Add a reverse reference to the DOM object
        base.$el.data( "pinet.cascadeSelect" , base );

        base.init = function () {
            base.myFunctionParam = myFunctionParam;

            base.options = $.extend({}, 
            $.pinet.cascadeSelect.defaultOptions, options);

            // Put your initialization code here
            console.log(1);
        };

        // Sample Function, Uncomment to use
        // base.functionName = function( paramaters ){
        //
        // };
        // Run initializer
        base.init();
    };

    $.pinet.cascadeSelect.defaultOptions = {
        myDefaultValue: ""
    };

    $.fn.pinet_cascadeSelect = function ( myFunctionParam, options ) {
        return this.each(function () {
            (new $.pinet.select(this,
            myFunctionParam, options));
        });
    };

})( jQuery );