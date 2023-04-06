function profile(){
    if(getCookie('fermentardoise_session') != ""){
        $("#connexion").remove();
        $("#profile-info").load("public_html/html_elements/profile.html", () => {
            $("#profile").click(() => {
                if($("#deconnexion").hasClass("hidden")){
                    $("#deconnexion").removeClass("hidden");
                }else{
                    $("#deconnexion").addClass("hidden");
                }
            });
            $("#deconnect").click(() => {
                deleteCookie( "fermentardoise_session", "/");
                window.location.replace("index.html");
            });
        });
    }
}

profile();