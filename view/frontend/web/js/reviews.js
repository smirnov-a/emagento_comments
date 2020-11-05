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
    moment
) {
    'use strict';

    //function getText() {
    //    return 'getText()';
    //}

    var popUp = null;
    var self;
    return Component.extend({
        reviews : ko.observableArray([]),
        time: ko.observable( Date() ),
        isFormPopupVisible: ko.observable(false),

        defaults: {
            template: 'Local_Comments/reviews_template',        // .html
            heading: 'Default Heading Text'
        },

        initialize: function () {
            self = this;

            this._super();
            this.incrementTime();
            // грузить с сервера
            this.loadReviews();
            //this.reviews = ['1', '2', 3];
            /*
            this.time = Date();
            //time is defined as observable
            this.observe(['time']);
            //periodically updater every second
            setInterval(this.flush.bind(this), 1000);
            */
            // подписаться на смену статуса попап
            this.isFormPopupVisible.subscribe(function (value) {
                if (value) {
                    self.getPopUp().openModal();
                }
            });
        },
        // форматирование даты
        getFormatDate: function(timestamp) {
            return moment(timestamp).format('DD.MM.YYYY');
        },
        incrementTime: function () {
            setInterval(function() {
                self.time( Date() );
            }, 1000);
        },
        showFormPopup: function() {
            //alert('popup');
            this.isFormPopupVisible(true);
            /*
            $('<div />').html('Modal Window Content')
                .modal({
                    title: 'My Title',
                    autoOpen: true,
                    closed: function () {
                        // on close
                    },
                    buttons: [{
                        text: 'Confirm',
                        attr: {
                            'data-action': 'confirm'
                        },
                        'class': 'action-primary',
                        click: function () {
                            this.closeModal();
                        }
                    }]
                });
            */
        },
        getPopUp: function () {
            var self = this;
            if (!popUp) {
                popUp = modal({
                    type: 'popup',
                    responsive: true,
                    innerScroll: true,
                    title: 'Title',
                    closed: function () {
                        self.isFormPopupVisible(false);
                    },
                    buttons: [
                        {
                            text: 'Send',
                            class: 'action primary'
                        },
                        {
                            text: 'Cancel',
                            class: 'action secondary'
                        }
                    ]
                }, $('#local-review-form'));
            }

            return popUp;
        },
        //flush: function(){
        //    this.time(Date());
        //},
        loadReviews: function () {
            //var reviews = ko.observableArray([]);
            storage.post(
                'local_reviews/ajax/getlist',
                {},
                false
            ).done(function (response) {
                var json = JSON.parse(response);
                console.log(json);
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
        }
    });
});

/*
define([
    'jquery',
    'mage/storage'
], function($, storage) {
   'use strict';

   var component = function(config) {
       //console.log(config);
       $(document).ready(function() {
           //alert('here');
           fullScreenLoader.startLoader();
           return storage.post(
               'local_reviews/reviews/getlist',
               JSON.stringify({count:config.count}),
               false
           ).done(function (data) {
               console.log(data);
               fullScreenLoader.stopLoader();
           }).fail(function() {
               fullScreenLoader.stopLoader();
           });
       });
   }

   return component;
});
*/
