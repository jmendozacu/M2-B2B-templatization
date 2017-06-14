define(
    [
        'uiComponent',
        'Magento_Checkout/js/model/payment/renderer-list'
    ],
    function (
        Component,
        rendererList
    ) {
        'use strict';
        rendererList.push(
            {
                type: 'chargeinaccount',
                component: 'Dckap_Chargeinaccount/js/view/payment/method-renderer/chargeinaccount'
            }
        );
        return Component.extend({});
    }
);