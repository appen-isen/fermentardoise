function listener_login() {
    document.getElementById("form_login").addEventListener("submit", function (event) {
        event.preventDefault();
        event.stopImmediatePropagation();
        console.log("connexion");
        let mail = document.getElementById("email").value;
        let upwd = document.getElementById("pwd").value;
        $.ajax({
            type: 'POST',
            url: 'api.php/login',
            data: {
                email: mail,
                pwd: upwd,
            },
            statusCode: {
                400: function () {
                    $("#error-zone").removeClass("hidden");
                }
            }
        }).done(() => {
            window.location.replace("index.html");
        });
    });
}

listener_login();
