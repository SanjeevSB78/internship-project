const registerBtn = document.getElementById("registerBtn");

registerBtn.addEventListener("click", function () {

    const username = document.getElementById("username").value.trim();
    const email = document.getElementById("email").value.trim();
    const password = document.getElementById("password").value;

    const messageEl = document.getElementById("message");
    messageEl.textContent = "";

    if (username === "") {
        messageEl.textContent = "Username is required";
        return;
    }

    if (!email.includes("@")) {
        messageEl.textContent = "Please enter a valid email";
        return;
    }

    if (password === "") {
        messageEl.textContent = "Password is required";
        return;
    }

    $.ajax({
        url: "php/register.php",
        type: "POST",
        dataType: "json",
        data: {
            username: username,
            email: email,
            password: password
        },
        success: function (response) {
            if (response.success) {
                messageEl.textContent = response.message + " Redirecting to login...";
                document.getElementById("username").value = "";
                document.getElementById("email").value = "";
                document.getElementById("password").value = "";

                setTimeout(function () {
                    window.location.href = "login.html";
                }, 1000);
            } else {
                messageEl.textContent = response.message;
            }
        },
        error: function () {
            messageEl.textContent = "Something went wrong. Please try again.";
        }
    });

});