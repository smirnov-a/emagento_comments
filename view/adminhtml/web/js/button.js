define([
    'jquery',
    'Magento_Ui/js/modal/modal',
    'Magento_Ui/js/modal/alert'
], function ($, modal, alert) {
    'use strict';

    return function(param) {
        $('#process').on('click', function () {
            $('body').trigger('processStart');
            $.ajax({
                url: param.url,
                type: 'POST',
                data: {
                    form_key: window.FORM_KEY
                },
                dataType: 'json',
                showLoader: true,
            }).done(function (data) {
                if (data.success) {
                    var popup = alert({
                        title: 'Success',
                        content: 'Success. Processed: '+data.processed,
                        autoOpen: true,
                    });
                    popup.on('alertclosed', function (event) {
                        if (data.processed) {
                            location.reload();
                        }
                    });
                }
            })
            .fail(function (jqXHR, textStatus, error) {
                alert({
                    title: 'Error',
                    content: 'Error: '+textStatus,
                    autoOpen: true,
                });
            })
            .always(function () {
                $('body').trigger('processStop');
            });
        });
    }
});
