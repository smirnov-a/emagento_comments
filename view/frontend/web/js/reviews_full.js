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

    function Review (data) {
        this.review_id = data.review_id;
        this.created_at = data.created_at;
        this.customer_id = data.customer_id;
        this.detail = data.detail;
        this.detail_id = data.detail_id;
        this.level = data.level;
        this.nickname = data.nickname;
        this.parent_id = data.parent_id;
        this.path = data.path;
        this.r_customer_id = data.r_customer_id;
        this.r_detail = data.r_detail;
        this.r_detail_id = data.r_detail_id;
        this.r_level = data.r_level;
        this.r_nickname = data.r_nickname;
        this.r_review_id = data.r_review_id;
        this.rating_votes = data.rating_votes;
        this.source = data.source;
        this.source_id = data.source_id;
    }

    return Component.extend({
        reviewsFull: ko.observableArray([]),
        totalRecords: ko.observable(0),
        curPage: ko.observable(1),
        totalPages: ko.observable(1),
        perPage: 15,    // params.count

        defaults: {
            template: 'Emagento_Comments/reviews_full_template',        // .html
            urlLoadReviews: '',
            storeId: 1,
        },

        initialize: function (params) {
            self = this;
            this._super();
            self.perPage = params.count;
        },
        loadReviewsFull: function (/*url,*/ page) {
            //console.log('load reviews full. url: '+url+'; page: '+page); return;
            //self.urlLoadReviews = url;
            page = page || 1;
            self.curPage(page);
            self.urlLoadReviews = self._getReviewUrl();

            var options = {
                type: 'popup',
                responsive: true,
                innerScroll: true,
                buttons: [{
                    text: $.mage.__('Close'),
                    class: 'primary action submit btn btn-default',
                    click: function () {
                        this.closeModal();
                    }
                }],
            };
            $.ajax({
                url: self.urlLoadReviews,
                type: 'GET',    //'POST',
                showLoader: true,
                dataType: 'json',
                /*
                data: {
                    limit: self.perPage,
                    p: page,
                },
                */
                complete: function (data) {
                    //console.log(data.responseJSON);
                    var json = JSON.parse(data.responseJSON);   //console.log(json);
                    //var reviews = [];
                    self.totalRecords(json.totalRecords);
                    //self.reviewsFull(json.items); //data.responseJSON);
                    // добалять в цикле
                    $.each(json.items, function (index, review) {
                        //reviews.push(new Review(review));
                        self.reviewsFull.push(new Review(review));
                    });
                    //self.reviewsFull(reviews);
                    self.totalPages(Math.round(json.totalRecords / self.perPage));
                    //console.log(self.curPage());
                    //console.log(self.totalPages());
                    //console.log(self.perPage);
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
            //console.log('loadMoreReview');
            //this.loadReviewsFull(self.urlLoadReviews, self.curPage() + 1);
            this.loadReviewsFull(self.curPage() + 1);
        },
        // строит ссылку на rest api '/rest/V1/ecomments/list/' +self.storeId+ '/' +1+ '/' +self.count
        _getReviewUrl: function () {
            return '/rest/V1/ecomments/list/' +self.storeId+ '/' +self.curPage()+ '/' +self.perPage;
        }
    });
});
