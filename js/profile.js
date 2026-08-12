$(document).ready(function () {

    const token = localStorage.getItem("token");

    if (!token) {
        alert("Please login first.");
        window.location.href = "login.html";
        return;
    }

    // LOAD PROFILE
    $.ajax({
        url: "php/profile.php",
        type: "GET",

        headers: {
            "Authorization": "Bearer " + token
        },

        success: function (response) {

            if (!response.success) {
                $("#message").text(response.message).show();
                return;
            }

            const profile = response.profile;

            $("#age").val(profile.age ?? "");
            $("#dob").val(profile.dob ?? "");
            $("#contact").val(profile.contact ?? "");
        },

        error: function (xhr) {
            console.error("GET error:", xhr.responseText);
        }
    });


    // SAVE PROFILE
    $("#saveProfileBtn").on("click", function () {

        $.ajax({
            url: "php/profile.php",
            type: "POST",

            headers: {
                "Authorization": "Bearer " + token
            },

            data: {
                age: $("#age").val(),
                dob: $("#dob").val(),
                contact: $("#contact").val()
            },

            success: function (response) {

                if (response.success) {
                    $("#message")
                        .text("Profile updated successfully!")
                        .removeClass("alert-danger")
                        .addClass("alert-success")
                        .show();
                } else {
                    $("#message")
                        .text(response.message)
                        .removeClass("alert-success")
                        .addClass("alert-danger")
                        .show();
                }
            },

            error: function (xhr) {
                console.error("POST error:", xhr.responseText);

                $("#message")
                    .text("Unable to update profile.")
                    .removeClass("alert-success")
                    .addClass("alert-danger")
                    .show();
            }
        });

    });


    // LOGOUT
    $("#logoutBtn").on("click", function () {

        $.ajax({
            url: "php/logout.php",
            type: "POST",

            headers: {
                "Authorization": "Bearer " + token
            },

            success: function (response) {

                if (response.success) {
                    localStorage.removeItem("token");
                    window.location.href = "login.html";
                } else {
                    $("#message").text(response.message).show();
                }
            },

            error: function (xhr) {
                console.error("Logout error:", xhr.responseText);
            }
        });

    });

});