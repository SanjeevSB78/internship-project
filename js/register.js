document.querySelectorAll(".password-toggle").forEach(function (button) {
    button.addEventListener("click", function () {
        const input = document.getElementById(button.dataset.target);
        const willShow = input.type === "password";

        input.type = willShow ? "text" : "password";
        button.querySelector(".eye-open").hidden = willShow;
        button.querySelector(".eye-closed").hidden = !willShow;
        button.setAttribute("aria-pressed", String(willShow));

        const label = button.dataset.target === "password"
            ? "password"
            : "confirm password";

        button.setAttribute(
            "aria-label",
            `${willShow ? "Hide" : "Show"} ${label}`
        );
    });
});

document.getElementById("registerBtn").addEventListener("click", function () {
    const username = document.getElementById("username").value.trim();
    const email = document.getElementById("email").value.trim();
    const password = document.getElementById("password").value;
    const confirmPassword = document.getElementById("confirmPassword").value;
    const messageEl = document.getElementById("message");

    messageEl.textContent = "";

    if (!username) {
        messageEl.textContent = "Username is required.";
        return;
    }

    if (!email || !email.includes("@")) {
        messageEl.textContent = "Please enter a valid email.";
        return;
    }

    if (!password) {
        messageEl.textContent = "Password is required.";
        return;
    }

    if (password !== confirmPassword) {
        messageEl.textContent = "Passwords do not match.";
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
            messageEl.textContent = response.message;

            if (response.success) {
                document.getElementById("username").value = "";
                document.getElementById("email").value = "";
                document.getElementById("password").value = "";
                document.getElementById("confirmPassword").value = "";

                setTimeout(function () {
                    window.location.href = "login.html";
                }, 1000);
            }
        },
        error: function (xhr) {
            console.error("Registration error:", xhr.responseText);
            messageEl.textContent =
                xhr.responseJSON?.message || "Registration failed. Please try again.";
        }
    });
});