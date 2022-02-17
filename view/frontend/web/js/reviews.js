/**
 * https://jason.codes/2019/07/magento-2-ui-component/
 */
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
        //time: ko.observable( Date() ),
        isFormPopupVisible: ko.observable(false),
        nickname: ko.observable(''),
        ratings: ko.observableArray([]),
        rating: ko.observable(0),

        defaults: {
            template: 'Emagento_Comments/reviews_template',        // .html
            formKey: $.cookie('form_key'),
            count: 0,
            urlLoadReviews: '/local_reviews/ajax/getlist',         // settings.url_getlist
            storeId: 1,
        },

        initialize: function (params) {
            self = this;
            this._super();
            var userName = this._getUserName();
            self.nickname(userName);
            if (params.storeId) {
                self.storeId = params.storeId;
            }
            // load
            self.count = params.count;
            //  '/rest/V1/ecomments/list/1/5'
            self.urlLoadReviews = '/rest/V1/ecomments/list/' +self.storeId+ '/' +1+ '/' +self.count;
            self.loadReviews(params.count);
            // subscribe on popup status
            self.isFormPopupVisible.subscribe(function (value) {
                if (value) {
                    self.getPopUp().openModal();
                }
            });
        },
        // get username first from session, then from client
        _getUserName: function () {
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
                            text: 'Отправить',
                            class: 'action-primary',
                            click: function (e) {
                                $('#form-local-comment').submit();
                            }
                        },
                        {
                            text: 'Отмена',
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
            $.ajax({
                url: this.urlLoadReviews,   // '/rest/V1/ecomments/list/1/5'
                type: 'GET',
                dataType: 'json',
                contentType: 'application/json',
                global: false
            })
            .done(function (response) {
                var json = JSON.parse(response);
                self.reviews(json);
                $('.shorten').shorten({
                    showChars: 200,
                    moreText: 'показать полностью',
                    lessText: 'свернуть'
                });

                self.getRatings();
            }).fail(function () {
                globalMessageList.addErrorMessage({
                    'message': $t('Could not get review list')
                });
            });
        },

        submitForm: function () {
            let $form = $('#form-local-comment');
            if ($form.valid()) {
                // save name into session
                var userName = this._getUserName();
                if (userName == '') {
                    sessionStorage.setItem('review_user_name', $('#nickname_field').val());
                }
                $.ajax({
                    url: '/local_reviews/review/save',
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
                                text: 'Ok',
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
                        //this.closeModal(true);
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

        getRatings: function () {
            // load ratings for form
            $.ajax({
                url: '/rest/V1/ecomments/ratings',
                type: 'GET',
                dataType: 'json',
                contentType: 'application/json',
            })
            .done(function (data) {
                var json = JSON.parse(data);
                self.rating(json.rating_id);
                self.ratings(json.options);
            })
            .fail(function () {
                globalMessageList.addErrorMessage({
                    'message': $t('Could not get ratings')
                });
            });
        },

        getLogoImage: function (review) {
            if (review.r_review_id) {
                return '/media/wysiwyg/company_20x20.jpg';
            }
            switch (review.source) {
                case 'flamp':
                    return '/media/wysiwyg/flamp_80x20.png';
                case 'yandex':
                    return '/media/wysiwyg/yandex_87x23.png';
                default:
                    return '/media/wysiwyg/company_20x20.jpg';
            }
        }
    });
});
