require(['jquery'], function ($, alert) {
    $(document).ready(function () {
        toggleHandlesVisibility();

        $('select[id*="bitrail_gateway_environment"]').on('change', toggleHandlesVisibility);
    
        function toggleHandlesVisibility()
        {
            $('input[id*="bitrail_gateway_vault_handle_"]').parent().parent().hide();
            $('input[id*="bitrail_gateway_vault_handle_'+$('select[id*="bitrail_gateway_environment"]').val()+'"]')
                .parent()
                .parent()
                .show();
        }
    });
});
