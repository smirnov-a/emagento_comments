define([
    'uiComponent',
    'ko',
    'mage/storage',
    'Magento_Ui/js/model/messageList',
    'mage/translate',
    'jquery',
    'Magento_Ui/js/modal/modal',
    'jquery_shorten',
    'moment',
    'Magento_Ui/js/modal/alert',
    'Magento_Customer/js/customer-data',
    'jquery/ui'
], function (
    Component,
    ko,
    storage,
    globalMessageList,
    $t,
    $,
    modal,
    shorten,
    moment,
    alert,
    customerData
) {
    'use strict';

    var popUp = null;
    var self;

    return Component.extend({
        reviews: ko.observableArray([]),
        totalRecord: ko.observable(0),
        isFormPopupVisible: ko.observable(false),
        nickname: ko.observable(''),
        ratings: ko.observableArray([]),
        rating: ko.observable(0),

        defaults: {
            template: 'Emagento_Comments/review_list',
            formKey: $.cookie('form_key'),
            page: 1,
            count: 10,
            urlLoadReviews: '/rest/V1/ecomments/get-reviews',
            urlSaveReview: '/local_reviews/review/create',
            storeId: 1,
            showChars: 200,
        },

        initialize: function (params) {
            self = this;
            this._super();
            self.nickname(this.getUserName());
            if (params.storeId) {
                self.storeId = params.storeId;
            }
            self.count = params.count;
            self.loadReviews(params.count);
            // subscribe on popup status
            self.isFormPopupVisible.subscribe(function (value) {
                if (value) {
                    self.getPopUp().openModal();
                }
            });
        },

        getUserName: function () {
            return sessionStorage.getItem('review_user_name') || customerData.get('customer')().firstname || '';
        },

        getFormatDate: function (timestamp) {
            return moment(timestamp).format('DD.MM.YYYY');
        },

        showFormPopup: function() {
            this.isFormPopupVisible(true);
        },

        getPopUp: function () {
            var self = this;
            if (!popUp) {
                popUp = modal({
                    type: 'popup',
                    responsive: true,
                    innerScroll: true,
                    title: $t('Adding Review'),
                    opened: function () {
                        $.validator.addMethod(
                            'rating-required', function (value) {
                                return value !== undefined;
                            }, $t('Please select your rating'));
                        $('#form-local-comment').validation({});
                    },
                    closed: function () {
                        self.isFormPopupVisible(false);
                    },
                    buttons: [
                        {
                            text: $t('Submit'),
                            class: 'action-primary',
                            click: function (e) {
                                $('#form-local-comment').submit();
                            }
                        },
                        {
                            text: $t('Cancel'),
                            class: 'action-secondary'
                        }
                    ],
                }, $('#local-review-form'));
            }

            return popUp;
        },

        getCount: function () {
            return this.count;
        },

        loadReviews: function (count) {
            var url = self.urlLoadReviews + '/' +self.page + '/' + count;
            $.ajax({
                url: url,
                type: 'GET',
                dataType: 'json',
                contentType: 'application/json',
            })
            .done(function (response) {
                self.totalRecord(response.reviews.total_count);
                self.reviews(response.reviews.items);
                self.ratings(self.parseRatings(response.ratings));
                $('.local-reviews .shorten').shorten({
                    showChars: self.showChars,
                    moreText: $t('Show full'),
                    lessText: $t('Show less')
                });
            })
            .fail(function () {
                globalMessageList.addErrorMessage({
                    'message': $t('Could not get review list')
                });
            });
        },

        submitForm: function () {
            let $form = $('#form-local-comment');
            if ($form.valid()) {
                // save name into session
                var userName = this.getUserName();
                if (userName === '') {
                    sessionStorage.setItem('review_user_name', $('#nickname_field').val());
                }
                $.ajax({
                    url: self.urlSaveReview,
                    data: $form.serializeArray(),
                    method: 'POST',
                    dataType: 'json',
                })
                .done(function (data) {
                    if (data.error) {
                        alert({
                            title: '',
                            content: data.error,
                            actions: {
                                always: function () {}
                            },
                            buttons: [{
                                text: $t('Ok'),
                                class: 'action',
                                click: function () {
                                    this.closeModal(true);
                                }
                            }],
                        });
                    } else if (data.message) {
                        // reset and close form (with saving nickname)
                        var oldUsername = self.nickname();
                        $('#form-local-comment')[0].reset();
                        $('#nickname_field').val(oldUsername);
                        self.getPopUp().closeModal();
                        alert({
                            title: '',
                            content: data.message,
                        });
                    }
                })
                .fail(function () {
                    globalMessageList.addErrorMessage({
                        'message': $t('Error saving review. Try later')
                    });
                });
            }
        },

        showAllReviews: function () {
            var $node = $('#reviews-popup-full').children('div');
            if ($node[0]) {
                var context = ko.contextFor($node[0]);
                if (context) {
                    context.$data.loadReviewsFull();
                }
            }
        },

        parseRatings: function (ratingsData) {
            if (!ratingsData) {
                return [];
            }

            return ratingsData.map(function (rating) {
                rating.value = ko.observable(0);
                rating.option_id = ko.observable(0);
                rating.options.forEach(function (option) {
                    option.option_id = ko.observable(option.option_id);
                    option.value = ko.observable(Number(option.value));
                    option.selected = ko.observable(false);
                });
                return rating;
            });
        },

        selectRating: function (ratingId, optionId, value) {
            self.ratings().forEach(function (rating) {
                if (rating.rating_id === ratingId) {
                    rating.option_id(optionId);
                    rating.value(value);
                    rating.options.forEach(function (option) {
                        option.selected(option.value() <= value);
                    });
                }
            });
        }
    });
});
