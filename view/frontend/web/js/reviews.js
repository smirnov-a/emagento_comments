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

    //function getText() {
    //    return 'getText()';
    //}

    var popUp = null;
    var self;

    return Component.extend({
        reviews: ko.observableArray([]),
        //time: ko.observable( Date() ),
        isFormPopupVisible: ko.observable(false),
        nickname: ko.observable(''),

        defaults: {
            template: 'Local_Comments/reviews_template',        // .html
            formKey: $.cookie('form_key'),
            ratings: [
                {code: 1, label: 'очень плохо'},
                {code: 2, label: 'плохо'},
                {code: 3, label: 'средне'},
                {code: 4, label: 'хорошо'},
                {code: 5, label: 'отлично'},
            ],
            count: 0,
            urlLoadReviews: 'local_reviews/ajax/getlist',
            //heading: 'Default Heading Text'
        },

        initialize: function (params) {
            //console.log(params);
            self = this;
            this._super();
            var userName = this._getUserName();
            //console.log(userName);
            self.nickname(userName);    //params.username);
            //this.incrementTime();
            // грузить с сервера
            self.count = params.count;
            self.loadReviews(params.count);
            /*
            this.time = Date();
            //time is defined as observable
            this.observe(['time']);
            //periodically updater every second
            setInterval(this.flush.bind(this), 1000);
            */
            // подписаться на смену статуса попап
            self.isFormPopupVisible.subscribe(function (value) {
                if (value) {
                    self.getPopUp().openModal();
                }
            });
        },
        // имя пользователя сперва берет из сессии, затем по клиенту
        _getUserName: function () {
            return sessionStorage.getItem('review_user_name') || customerData.get('customer')().firstname || '';
        } ,
        // форматирование даты
        getFormatDate: function (timestamp) {
            return moment(timestamp).format('DD.MM.YYYY');
        },
        /* test
        incrementTime: function () {
            setInterval(function() {
                self.time( Date() );
            }, 1000);
        },
        */
        showFormPopup: function() {
            //alert('popup');
            this.isFormPopupVisible(true);
        },
        getPopUp: function () {
            var self = this;
            if (!popUp) {
                //var data = $('#form-local-comment').serializeArray();
                //data['form_key'] = $.mage.cookies.get('form_key');
                popUp = modal({
                    type: 'popup',
                    responsive: true,
                    innerScroll: true,
                    title: 'Добавление отзыва',
                    closed: function () {
                        self.isFormPopupVisible(false);
                    },
                    buttons: [
                        {
                            text: 'Записать',
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
                    /*
                    opened: function () {
                        console.log('opened');
                        var nickname = self.nickname();
                        console.log(nickname);
                        if (self.nickname() == '') {
                            var userName = self._getUserName();
                            console.log(userName);
                            if (userName) {
                                self.nickname(userName);
                            }
                        }
                    },
                    */
                }, $('#local-review-form'));    // содержимое модального окна
            }

            return popUp;
        },
        //flush: function(){
        //    this.time(Date());
        //},
        getCount: function () {
            return this.count;
        },
        loadReviews: function (count) {
            //console.log('loadReviews');
            //var reviews = ko.observableArray([]);
            // post(url, data, global, contentType, headers)
            $.ajax({
                url: this.urlLoadReviews,   //'local_reviews/ajax/getlist',
                type: 'POST',
                data: {
                    count: count
                },
                //dataType: 'json', no it's not json!
                global: false
            })
            /*
            storage.post(
                'local_reviews/ajax/getlist',
                data,
                false
            )*/
            .done(function (response) {
                var json = JSON.parse(response);
                //console.log(json);
                self.reviews(json);
                // повесить shorten
                $('.shorten').shorten({
                    showChars: 200,
                    moreText: 'показать полностью',
                    lessText: 'свернуть'
                });
                /*
                if (json) {
                    self.reviews([]);
                    _.each(json, function (v, i) {
                        console.log(i);
                        console.log(v);
                        self.reviews.push({i: v});
                    });
                }
                */
            }).fail(function () {
                globalMessageList.addErrorMessage({
                    'message': $t('Could not get review list')
                });
            });
        },
        submitForm: function () {
            var $form = $('#form-local-comment');
            if ($form.valid()) {
                // прописать если нужно имя в сессию
                var userName = this._getUserName();
                if (userName == '') {
                    sessionStorage.setItem('review_user_name', $('#nickname_field').val());
                }
                $.ajax({
                    url: 'local_reviews/review/save',
                    data: $('#form-local-comment').serializeArray(),
                    method: 'POST',
                    dataType: 'json',
                    //contentType:'application/json; charset=utf-8',
                })
                .done(function (data) {
                    //console.log(data);
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
                        // сбросить и закрыть форму (сохранить имя пользователя)
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
                    //console.log('error');
                    alert({
                        title: '',
                        content: 'Произошла ошибка. Попробуйте позже',  //data,
                    });
                });
            }
        },
        showAllReviews: function () {
            //console.log('showAllReviews');
            var $node = $('#reviews-popup-full').children('div');
            //console.log($node);
            if ($node[0]) {
                var context = ko.contextFor($node[0]);
                //console.log(context);
                if (context) {
                    context.$data.loadReviewsFull(this.urlLoadReviews);
                }
            }
        }
    });
});
