define([
    'jquery',
    'Magento_Ui/js/modal/modal',
    'Magento_Ui/js/modal/alert'
], function (
    $,
    modal,
    alert
) {
    'use strict';

    return function(param) {
        //console.log('processReviews');
        //console.log(param.url);
        $('#process').on('click', function () {
            //console.log('clicked');
            // open popup w/loader
            var $modal = $('<div/>').html(param.html_templ);
            $modal.modal({
                    type: 'popup',
                    autoOpen: true,
                    responsive: true,
                    innerScroll: true,
                    //title: 'popup modal',
                    opened: function () {
                        //console.log('opened');
                        // при открытии сразу запустить работу
                        $.ajax({
                            url: param.url,
                            type: 'POST',
                            data: {
                                form_key: window.FORM_KEY
                            },
                            dataType: 'json',
                        })
                        .done(function (data) {
                            console.log(data);
                            if (data.success) {
                                alert({
                                    title: 'Success',
                                    content: 'Success. Processed: '+data.processed,
                                    autoOpen: true
                                });
                            }
                        })
                        .fail(function (jqXHR, textStatus, error) {
                            console.log(error);
                            alert({
                                title: 'Error',
                                content: 'Error: '+textStatus,
                                autoOpen: true
                            });
                        })
                        .always(function () {
                            $modal.modal('closeModal');
                        });
                    },
                    buttons: []
                    //buttons: [{
                    //    text: 'Ok',
                    //    class: '',
                    //    click: function () {
                    //        this.closeModal();
                    //    }
                    //}]
                });
        });
    }
});
