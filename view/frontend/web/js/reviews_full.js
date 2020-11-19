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

    var self;

    return Component.extend({
        reviewsFull: ko.observableArray([]),
        totalRecords: ko.observable(0),
        curPage: ko.observable(1),
        totalPages: 1,
        perPage: 15,

        defaults: {
            template: 'Local_Comments/reviews_full_template',        // .html
        },

        initialize: function (params) {
            self = this;
            this._super();
            //console.log('here');
        },
        loadReviewsFull: function (url) {
            //console.log('load reviews full. url: '+url); //return;
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
                //dataType: 'json',
                data: {
                    count: self.perPage,
                    p: self.curPage(),
                },
                complete: function (data) {
                    //console.log(data.responseJSON);
                    var json = JSON.parse(data.responseJSON);   //console.log(json);
                    self.totalRecords(json.totalRecords);  //console.log(self.totalRecords()); return;
                    self.reviewsFull(json.items); //data.responseJSON);
                    self.totalPages = Math.round(json.totalRecords / self.perPage);
                    console.log(self.curPage());
                    console.log(self.totalPages);
                    console.log(self.perPage);
                    //console.log(self.reviewsFull());
                    //console.log(self.totalRecords());
                    $('#reviews-popup-full')
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
        },
        loadMoreReview: function () {
            console.log('loadMoreReview');
        }
    });
});
