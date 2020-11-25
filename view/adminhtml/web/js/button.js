define([
    'jquery',
    'Magento_Ui/js/modal/modal',
    'jquery/jquery.cookie'
], function (
    $,
    modal
) {
    'use strict';

    return function(param) {
        //console.log('processReviews');
        console.log(param.url);
        $('#process').on('click', function () {
            console.log('clicked');
            // open popup
            $('<div/>').html('qqq').modal({
                type: 'popup',
                autoOpen: true,
                responsive: true,
                innerScroll: true,
                //title: 'popup modal',
                opened: function () {
                    console.log('opened');
                    $.ajax({
                        url: param.url,
                        type: 'POST',
                        data: {
                            form_key: $.cookie('form_key')
                        },
                        success: function (data) {
                            console.log(data);
                        },
                        error: function (err) {
                            console.log(err)
                        }
                    });
                },
                buttons: [{
                    text: 'Ok',
                    class: '',
                    click: function () {
                        this.closeModal();
                    }
                }]
            });
            //$.ajax({

            //})
        });
    }
});
