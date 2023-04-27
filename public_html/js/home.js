function profile(){
    if(getCookie('fermentardoise_session') != ""){
        $("#connexion").remove();
        $("#profile-info").load("public_html/html_elements/profile.html", () => {
            $.ajax({
                type: 'POST',
                url: 'api.php/getEmail',
                headers: {
                    Authorization: 'Bearer ' + getCookie('fermentardoise_session')
                }
            }).done((data) => {
                $("#profil_name").append(data);
            });
            $("#deconnect").click(() => {
                $.ajax({
                    type: 'POST',
                    url: 'api.php/logout',
                    headers: {
                        Authorization: 'Bearer ' + getCookie('fermentardoise_session')
                    }
                }).done((data) => {
                    window.location.replace("index.html");
                });
            });
        });
    }
}

profile();