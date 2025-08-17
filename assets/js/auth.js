$(document).ready(function() {
    $("#registerForm").on("submit", function(e) {
        e.preventDefault();

        var login = $("#reg-login").val();
		var email = $("#reg-mail").val();
        var password = $("#reg-password").val();
        var captchaResponse = $("#g-recaptcha-response").val();

        $.ajax({
            type: "POST",
            url: "backend/auth/register.php",
            data: {
                login: login,
                password: password,
                mail: email,
                "g-recaptcha-response": captchaResponse
            },
            dataType: "json",
            success: function(response) {
                if (response.status) {
                    window.location.href = "cabinet";
                } else {
                    $('#result').html(response);
                }
            },
            error: function() {
                $('#result').html(response);
            }
        });
    });

    $("#authForm").on("submit", function(e) {
        e.preventDefault();

        var login = $("#log-login").val();
        var password = $("#log-password").val();

        $.ajax({
            type: "POST",
            url: "backend/auth/login.php",
            data: {
                login: login,
                password: password
            },
            dataType: "json",
            success: function(response) {
                if (response.status) {
                    $("#result").html('<p style="color: green; font-weight: 550">' + response.Message  + '</p>');
                    window.location.href = "cabinet";
                } else {
                    $("#result").html('<p style="color: red; font-weight: 550">' + response.Message  + '</p>');
                }
            },
            error: function() {
                $("#result").html('<p style="color: red; font-weight: 550">' + response.Message  + '</p>');
            }
        });
    });
    $("#recoveryForm").on("submit", function(e) {
        e.preventDefault();

        var recmail = $("#rec-mail").val();
        var captchaResponse = $("#g-recaptcha-response").val();
        $.ajax({
            type: "POST",
            url: "backend/auth/recovery.php",
            data: {
                recmail: recmail,
                "g-recaptcha-response": captchaResponse
            },
            dataType: "json",
            success: function(response) {
                if (response.status) {
                    $('#result').html(response);
                } else {
                    $('#result').html(response);
                }
            },
            error: function() {
                $('#result').html(response);
            }
        });
    });
});