function loadUsers() {
    $.ajax({
        url: 'api/v1/site/admin/global/get_users.php',
        method: 'GET',
        dataType: 'json',
        success: function(data) {
            const usersTableBody = $('#usersTable tbody');
            usersTableBody.empty();

            if (data.status) {
                data.users.forEach(user => {
                    const row = `<tr><td>${user.nickname}</td><td>${user.till}</td><td>${user.uid}</td><td>${user.email}</td><td>${user.role}</td></tr>`;
                    usersTableBody.append(row);
                });
            } else {
                const row = `<tr><td colspan="2">${data.message}</td></tr>`;
                usersTableBody.append(row);
            }
        },
        error: function(xhr, status, error) {
            console.error('Error fetching users:', error);
            const usersTableBody = $('#usersTable tbody');
            usersTableBody.html('<tr><td colspan="2">Ошибка при загрузке пользователей</td></tr>');
        }
    });
}

function openTab(tabName) {
    var i, tabContent, tabButtons;
    tabContent = $(".tab-content");
    tabContent.hide();
    
    tabButtons = $(".tab-button");
    tabButtons.removeClass("active");
    
    $("#" + tabName).show();
    event.currentTarget.className += " active";

    if (tabName === 'users') {
        loadUsers();
    }
}

function openSubTab(subTabName) {
    $(".sub-tab-content").hide();
    $("#" + subTabName).show();
}

function generateKey(event) {
    event.preventDefault();
    var form = event.target;
    var formData = $(form).serialize();
    
    $.ajax({
        type: 'POST',
        url: 'api/v1/site/admin/keysystem/gen_key.php',
        data: formData,
        dataType: 'json',
        success: function(response) {
            console.log(response);
            if (response.status === true) {
                $('#keyResult').html('Сгенерированный ключ: ' + response.key);
            } else {
                $('#keyResult').html('Ошибка: ' + response.message);
            }
        },
        error: function(xhr, status, error) {
            console.error('Error:', error);
            $('#keyResult').html('Ошибка при генерации ключа');
        }
    });
}

function giveSub(event) {
    event.preventDefault();
    var form = event.target;
    var formData = new FormData(form);
    
    $.ajax({
        url: 'api/v1/site/admin/global/give_sub.php',
        method: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        dataType: 'json',
        success: function(data) {
            console.log(data);
            if (data.status === true) {
                $('#subGiveResult').html(data.message);
            } else {
                $('#subGiveResult').html('Ошибка: ' + data.message);
            }
        },
        error: function(error) {
            console.error('Error:', error);
            $('#subGiveResult').html('Ошибка при выдаче подписки');
        }
    });
}

function hwidRes(event) {
    event.preventDefault();
    var form = event.target;
    var formData = new FormData(form);
    
    $.ajax({
        url: 'api/v1/site/admin/global/hwid_res.php',
        method: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        dataType: 'json',
        success: function(data) {
            console.log(data);
            if (data.status === true) {
                $('#hwidResult').html(data.message);
            } else {
                $('#hwidResult').html('Ошибка: ' + data.message);
            }
        },
        error: function(error) {
            console.error('Error:', error);
            $('#hwidResult').html('Ошибка при выдаче подписки');
        }
    });
}

function resSub(event) {
    event.preventDefault();
    var form = event.target;
    var formData = new FormData(form);
    
    $.ajax({
        url: 'api/v1/site/admin/global/res_sub.php',
        method: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        dataType: 'json',
        success: function(data) {
            console.log(data);
            if (data.status === true) {
                $('#resSubResult').html(data.message);
            } else {
                $('#resSubResult').html('Ошибка: ' + data.message);
            }
        },
        error: function(error) {
            console.error('Error:', error);
            $('#resSubResult').html('Ошибка при выдаче подписки');
        }
    });
}

function roleGive(event) {
    event.preventDefault();
    var form = event.target;
    var formData = new FormData(form);
    
    $.ajax({
        url: 'api/v1/site/admin/global/give_role.php',
        method: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        dataType: 'json',
        success: function(data) {
            console.log(data);
            if (data.status === true) {
                $('#roleResult').html(data.message);
            } else {
                $('#roleResult').html('Ошибка: ' + data.message);
            }
        },
        error: function(error) {
            console.error('Error:', error);
            $('#roleResult').html('Ошибка при выдаче подписки');
        }
    });
}

function createPromo(event) {
    event.preventDefault();
    var form = event.target;
    var formData = new FormData(form);
    
    $.ajax({
        url: 'api/v1/site/admin/promocode/create_promo.php',
        method: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        dataType: 'json',
        success: function(data) {
            console.log(data);
            if (data.status === true) {
                $('#CpromoResult').html(data.message);
            } else {
                $('#CpromoResult').html('Ошибка: ' + data.message);
            }
        },
        error: function(error) {
            console.error('Error:', error);
            $('#CpromoResult').html('Ошибка при выдаче подписки');
        }
    });
}

$(".tab-button")[0].click();