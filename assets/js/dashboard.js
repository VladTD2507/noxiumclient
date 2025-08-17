const promoCodeInput = $("#promoCodeInput");
const promoHiddenInput = $("#promo");
const promoResult = $("#promoResult");

promoCodeInput.on('input', function() {
    const promoCode = $(this).val();
    promoHiddenInput.val(promoCode); 
});


document.addEventListener('DOMContentLoaded', function() {
    const options = document.querySelectorAll('.payment-option');
    const radios = document.querySelectorAll('input[name="payment_system"]');

    function updateSelectedOption() {
        options.forEach(option => option.classList.remove('payment-option-selected'));
        radios.forEach(radio => {
            if (radio.checked) {
                const option = document.querySelector(`.payment-option input[id="${radio.id}"]`).parentElement;
                option.classList.add('payment-option-selected');
            }
        });
    }

    radios.forEach(radio => {
        radio.addEventListener('change', function() {
            updateSelectedOption();
        });
    });

    options.forEach(option => {
        option.addEventListener('click', function() {
            const radio = this.querySelector('input[type="radio"]');
            radio.checked = true;
            updateSelectedOption();
        });
    });

    updateSelectedOption();
});


const menuLinks = document.querySelectorAll('.personal-account-menu-link');
menuLinks.forEach(link => {
    link.addEventListener('click', function(e) {
        e.preventDefault();
        menuLinks.forEach(link => link.classList.remove('active'));
        this.classList.add('active');
        const targetId = this.getAttribute('data-target');
        document.querySelectorAll('.content-section').forEach(section => {
            section.classList.remove('active');
        });
        document.getElementById(targetId).classList.add('active');
    });
});

$(document).ready(function() {
    $("#change_password_form").on("submit", function(e) {
        e.preventDefault();

        var password = $("#change_password_form input[name='password']").val();

        $.ajax({
            type: "POST",
            url: "api/v1/site/change_password.php",
            data: {
                password: password,
            },
            dataType: "json",
            success: function(response) {
                if (response.status) {
                    $("#result").html('<p style="color: green; font-weight: 550">Пароль успешно обновлен!</p>');
                } else {
                    $("#result").html(response.message);
                }
            },
            error: function(xhr, status, error) {
                $("#result").html("Error: " + error);
            }
        });
    });
    $("#activate-from").on("submit", function(e) {
        e.preventDefault();

        var key = $("#key").val();

        $.ajax({
            type: "POST",
            url: "api/v1/site/activate_key.php",
            data: {
                key: key,
            },
            dataType: "json",
            success: function(response) {
                if (response.status === 'ok') {
                    $("#activate_resoult").html(response.message);
                } else {
                    $("#activate_resoult").html(response.message);
                }
            },
            error: function(xhr, status, error) {
                $("#activate_resoult").html("Error: " + error);
            }
        });
    });
    $("#memory-form").on("submit", function(e) {
        e.preventDefault();

        var formData = $(this).serialize();

        $.ajax({
            type: "POST",
            url: "api/v1/site/set_memory.php",
            data: formData, 
            dataType: "json",
            success: function(response) {
                if (response.status === 'ok') {
                    $("#memory_result").html(response.message);
                } else {
                    $("#memory_result").html(response.message);
                }
            },
            error: function(xhr, status, error) {
                $("#memory_result").html("Error: " + error);
            }
        });
    });
});