
<script>
    var prop = $.parseJSON('<?php echo $selectors; ?>');
    $("form").submit(function(event) {
        event.preventDefault();

        // Remove previous error classes
        if(prop.error.fieldGroup.length != 0) {
            $("." + prop.error.fieldGroup).removeClass(prop.error.fieldGroup);
        }
        if(prop.error.fieldLabel.length != 0) {
            $("." + prop.error.fieldLabel).removeClass(prop.error.fieldLabel);
        }
        // Remove previous error messages.
        $("." + prop.node.errortext).remove();

        // Parse form data for AJAX request.
        var formData = $(this).serialize();

        // Apply waiting alert to the message div.
        $("#" + prop.node.formMessage).removeClass();
        $("#" + prop.node.formMessage).addClass(prop.alert.waiting);
        $("#" + prop.node.formMessage).html("<p>Please wait ...</p>");
        $("#" + prop.node.formMessage).fadeIn();

        // Scroll to message div.
        $("html, body").animate({
            scrollTop: $("#" + prop.node.formMessage).offset().top - 20
        }, 150);
        // Send AJAX Request.
        $.ajax({
            type: "POST",
            url: $("form").attr("action"),
            data: formData
        }).done(function(response) {
            // FadeOut waiting message and then process response.
            $("#" + prop.node.formMessage).fadeOut(500, function() {  
                var res = $.parseJSON(response);
                // Append message and remove waiting class
                $("#" + prop.node.formMessage).html(res.message);
                $("#" + prop.node.formMessage).removeClass();

                if(res.success) {
                    $("#" + prop.node.formMessage).addClass(prop.alert.success);
                }
                else {
                    <?php if($captcha) { ?>
                        // If more than signature errors are present, append expiration message.
                        if(!(prop.node.syserror in res.errors)) {
                            $("#" + prop.node.captcha).parent().addClass(prop.error.fieldGroup);
                            $("#" + prop.node.captcha).after("<span class='" + prop.error.fieldFeedback + " " + prop.node.errortext + "'>Your ReCAPTCHA validation has expired. Please retry above.</span>");
                        }
                        grecaptcha.reset();
                    <?php } ?>
                    $("#" + prop.node.formMessage).addClass(prop.alert.error);
                    for (var key in res.errors) {
                        var id = "#" + key;
                        if($(id).length == 0) {
                            /* If the error message does not refer to a specific element, it is a system error and must be applied
                            to the form message box. */
                            $("#" + prop.node.formMessage).html(res.errors[key]);
                        }
                        else {
                            // Add the field block error class.
                            $(id).parent().addClass(prop.error.fieldGroup);
                            // Add error class to the label.
                            $(id).parent().find("label").addClass(prop.error.fieldLabel);
                            // Add error message.
                            $(id).parent().append("<span class='" + prop.error.fieldFeedback + " " + prop.node.errortext + "'>" + res.errors[key] + "</span>");
                        }
                    }
                }

                // Fade in message
                $("#" + prop.node.formMessage).fadeIn();
            });
        });
    })
</script>

<?php if($captcha) { ?>
    <script src='https://www.google.com/recaptcha/api.js'></script>
    <script>
        window[prop.node.captcha] = function() {
        }
    </script>
<?php } ?>
