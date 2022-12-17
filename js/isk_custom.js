jQuery(document).ready(function($) {

    function ajax_call() {


        /* var data = {
            'action': 'my_action',
            'whatever': 1234
        }; */

        if($("#add_link").valid() == false) {
            console.log("not valid");
            return false;
        }

        var data = $("#add_link").serialize();
    
        // since 2.8 ajaxurl is always defined in the admin header and points to admin-ajax.php
        jQuery.post(isk_ajax_script_param.ajax_url, data, function(response) {
            //alert('Got this from the server: ' + JSON.parse(response));
            console.log(JSON.parse(response));
            var response = JSON.parse(response);
            //if(response.status == 404) {
                $("#result").html(response.msg);
                $("#test_link_url").val(response.data.short_name)
            //}
        });
    }

    $("#create_link").on('click', function(){
        ajax_call();
    });

    $("#test_link").on('click', function(){
        let url = $("#test_link_url").val();
        if(url != '') {
            window.open(url, '_blank');
        }
    });

    $("#clear").on('click', function(){
        $("#add_link").get(0).reset();
        $("#result").html("Result");
        $("#test_link_url").val('');
    });
    
});