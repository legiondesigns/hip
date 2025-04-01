jQuery(document).ready(function($) {
    $("#btn-etsy-shop-delete-cache").click(function() {
        var original_text = $("#btn-etsy-shop-delete-cache").html();
        $("#btn-etsy-shop-delete-cache").html("In progress...");
        $("#etsy-shop-delete-cache-result").html("");
        $("#btn-etsy-shop-delete-cache").prop( "disabled", true );
        $.post(etsy_shop_admin_ajax.ajax_url, {
            _ajax_nonce: etsy_shop_admin_ajax.nonce,
            action: "etsy_shop_delete_cache"
        }, function(data) {
            $("#etsy-shop-delete-cache-result").html(data);
            $("#btn-etsy-shop-delete-cache").html(original_text);
            $("#btn-etsy-shop-delete-cache").prop( "disabled", false );
        });

        return false;
    });
});
