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
    alert
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
        nickname: ko.observable(),

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
            //heading: 'Default Heading Text'
        },

        initialize: function (params) {
            //console.log(params);
            self = this;
            this._super();
            var userName = sessionStorage.getItem('review_user_name') || customerData.get('customer')().firstname || '';
            self.nickname(userName);    //params.username);
            //this.incrementTime();
            // грузить с сервера
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
        // форматирование даты
        getFormatDate: function(timestamp) {
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
                    ]
                }, $('#local-review-form'));    // содержимое модального окна
            }

            return popUp;
        },
        //flush: function(){
        //    this.time(Date());
        //},
        loadReviews: function (count) {
            //var reviews = ko.observableArray([]);
            // post(url, data, global, contentType, headers)
            $.ajax({
                url: 'local_reviews/ajax/getlist',
                type: 'POST',
                data: {
                    count: count
                },
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
                        // сбросить и закрыть форму
                        $('#form-local-comment')[0].reset();
                        self.getPopUp().closeModal();
                        //this.closeModal(true);
                        alert({
                            title: '',
                            content: data.message,
                        });
                    }
                    //else {
                    //    console.log('here2');
                    //    console.log(typeof data);
                    //}
                })
                .fail(function () {
                    //console.log('error');
                    alert({
                        title: '',
                        content: 'Произошла ошибка. Попробуйте позже',  //data,
                    });
                });
            }
            /*
            utils.ajaxSubmit({
                url: 'local_reviews/review/save',
                data: data,
            }, {
                ajaxSaveType: 'default',    // 'simple'
            });
            */
        }
    });
});
