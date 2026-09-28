$(document).ready(function () {
    const token = localStorage.getItem("token");

    if (!token) {
        alert("Please login first.");
        window.location.href = "login.html";
        return;
    }

    function showMessage(message, isSuccess = false) {
        $("#message")
            .text(message)
            .removeClass("alert-info alert-success alert-danger")
            .addClass(isSuccess ? "alert-success" : "alert-danger")
            .show();
    }

    function calculateAge(dob) {
        const birthDate = new Date(`${dob}T00:00:00`);
        const today = new Date();

        let age = today.getFullYear() - birthDate.getFullYear();

        const beforeBirthday =
            today.getMonth() < birthDate.getMonth() ||
            (today.getMonth() === birthDate.getMonth() &&
                today.getDate() < birthDate.getDate());

        if (beforeBirthday) {
            age--;
        }

        return age;
    }

    // Update age whenever the user changes the date of birth.
    $("#dob").on("input change", function () {
        const dob = $(this).val();

        if (!dob) {
            $("#age").val("").prop("readonly", false);
            $("#message").hide();
            return;
        }

        const age = calculateAge(dob);

        if (age < 0 || age > 120) {
            $("#age").val("");
            showMessage("Date of birth must be valid and age cannot exceed 120.");
            return;
        }

        $("#age").val(age).prop("readonly", true);
        $("#message").hide();
    });

    // Load the saved profile.
    $.ajax({
        url: "php/profile.php",
        type: "GET",
        headers: {
            Authorization: "Bearer " + token
        },
        success: function (response) {
            if (!response.success) {
                showMessage(response.message || "Unable to load profile.");
                return;
            }

            const profile = response.profile;

            $("#dob").val(profile.dob || "");
            $("#contact").val(profile.contact || "");

            if (profile.dob) {
                const age = calculateAge(profile.dob);

                if (age >= 0 && age <= 120) {
                    $("#age").val(age).prop("readonly", true);
                } else {
                    $("#age").val("").prop("readonly", true);
                    showMessage("The saved date of birth is invalid.");
                }
            } else {
                $("#age").val(profile.age ?? "").prop("readonly", false);
            }
        },
        error: function (xhr) {
            console.error("Profile load error:", xhr.responseText);
            showMessage("Unable to load profile.");
        }
    });

    // Save the profile.
    $("#saveProfileBtn").on("click", function () {
        const dob = $("#dob").val();
        let age = $("#age").val().trim();

        if (dob) {
            age = String(calculateAge(dob));

            if (Number(age) < 0 || Number(age) > 120) {
                showMessage("Date of birth must be valid and age cannot exceed 120.");
                return;
            }

            $("#age").val(age).prop("readonly", true);
        } else if (
            age !== "" &&
            (!/^\d+$/.test(age) || Number(age) > 120)
        ) {
            showMessage("Age must be a whole number between 0 and 120.");
            return;
        }

        $.ajax({
            url: "php/profile.php",
            type: "POST",
            headers: {
                Authorization: "Bearer " + token
            },
            data: {
                age: age,
                dob: dob,
                contact: $("#contact").val()
            },
            success: function (response) {
                if (response.success) {
                    showMessage("Profile updated successfully!", true);
                } else {
                    showMessage(response.message || "Unable to update profile.");
                }
            },
            error: function (xhr) {
                console.error("Profile save error:", xhr.responseText);

                const response = xhr.responseJSON;
                showMessage(
                    response?.message || "Unable to update profile."
                );
            }
        });
    });

    // Log out.
    $("#logoutBtn").on("click", function () {
        $.ajax({
            url: "php/logout.php",
            type: "POST",
            headers: {
                Authorization: "Bearer " + token
            },
            success: function (response) {
                if (response.success) {
                    localStorage.removeItem("token");
                    window.location.href = "login.html";
                } else {
                    showMessage(response.message || "Unable to log out.");
                }
            },
            error: function (xhr) {
                console.error("Logout error:", xhr.responseText);
                showMessage("Unable to log out.");
            }
        });
    });
});