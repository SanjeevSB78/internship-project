const loginBtn = document.getElementById("loginBtn");

loginBtn.addEventListener("click", function () {

    const username = document.getElementById("username").value.trim();
    const password = document.getElementById("password").value;

    if (username === "") {
        alert("Username is required");
        return;
    }

    if (password === "") {
        alert("Password is required");
        return;
    }

    $.ajax({
        url: "php/login.php",
        type: "POST",
        dataType: "json",
        data: {
            username: username,
            password: password
        },
        success: function (response) {
            if (response.success) {
                localStorage.setItem("token", response.token);
                window.location.href = "profile.html";
            } else {
                alert(response.message);
            }
        },
        error: function () {
            alert("Something went wrong!");
        }
    });

});