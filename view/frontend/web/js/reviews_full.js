define([
    'uiComponent',
    'ko',
    'jquery',
    'Magento_Ui/js/modal/modal'
], function (
    Component,
    ko,
    $,
    modal
) {
    'use strict';

    return Component.extend({
        reviews: ko.observableArray([]),

        defaults: {
            template: 'Local_Comments/reviews_full_template',        // .html
        },

        initialize: function (params) {
            this._super();
            //console.log('here');
        },
        loadReviews: function (url) {
            var self = this;
            console.log('load reviews full. url: '+url);
            var options = {
                type: 'popup',
                responsive: true,
                innerScroll: true,
                buttons: [{
                    text: $.mage.__('Continue'),
                    class: 'primary action submit btn btn-default',
                    click: function () {
                        this.closeModal();
                    }
                }],
            };
            $.ajax({
                url: url,
                type: 'POST',
                showLoader: true,
                dataType: 'json',
                data: {
                    count: 15
                },
                complete: function (data) {
                    //console.log(data.responseJSON);
                    self.reviews(data.responseJSON);
                    $('#reviews-popup')
                        //.html(data.responseText)
                        .modal(options)
                        .modal('openModal');
                },
                error: function (xhr, status, errorThrown) {
                    console.log(xhr);
                    console.log(status);
                    console.log(errorThrown);
                }
            });
        }
    });
});
