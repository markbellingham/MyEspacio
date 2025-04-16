import {notify} from "../../ts/framework/Notification";
import RequestHeaders from "../../ts/framework/RequestHeaders";

const loginBtn = document.querySelector("#login-btn");
let task;
const loginModal = new bootstrap.Modal(document.getElementById("loginModal"));
document.querySelector("#loginModal").addEventListener("shown.bs.modal", function () {
    document.querySelector("#login-email").focus();
});

loginBtn.addEventListener("click", event => {
    event.preventDefault();
    task = loginBtn.getAttribute("data-task");
    if (task === "login") {
        loginModal.show();
        document.querySelector("#login-email").focus();
        return;
    }
    if (task === "logout") {
        return logout();
    }
});

document.querySelector("#login-submit-btn").addEventListener("click", login);

function login(event)
{
    event.preventDefault();
    let formData = new FormData(event.target.form);
    formData = Object.fromEntries(formData.entries());
    if (formData.email === "") {
        notify.error("Please fill in your email address");
        return;
    }

    const headers = RequestHeaders.jsonWithToken();
    headers.append("Content-Type", "application/x-www-form-urlencoded");
    fetch("/login", {
        method: "POST",
        headers: headers,
        body: JSON.stringify(formData)
    })
        .then(response => response.json())
        .then(data => {
            if (!response.ok) {
                notify.error(`Sorry, there was a problem.<br > ${data.message}`);
                return;
            }

            if (formData.phone_code === "") {
                requestPhoneCode();
                return;
            }

            toggleLoginBtnState(data.username);
            resetLoginModal();
        });
}

function requestPhoneCode()
{
    notify.success("Please check your email for a code or login link.");
    document.querySelector("#phone-code-row").toggleAttribute("hidden");
    document.querySelector("#phone-code").focus();
}

function resetLoginModal()
{
    loginModal.hide();
    document.querySelector("#phone-code-row").toggleAttribute("hidden");
    document.querySelector("#login-form").reset();
}

function toggleLoginBtnState(username)
{
    loginBtn.setAttribute("data-task", "logout");
    loginBtn.innerText = "Log Out";
    const markup = `<span>Welcone ${username}</span> `;
    loginBtn.insertAdjacentHTML("beforebegin", markup);
}

function logout()
{
    sendServerRequest("/logout", null, response => {
        if (response.success) {
            notify.success("You have been logged out");
            loginBtn.setAttribute("data-task", "login");
            loginBtn.innerText = "Log In";
            task = "login";
            loginBtn.parentNode.querySelector("span").remove();
        } else {
            notify.error("Sorry, there was a problem processing your request");
        }
    });
}

function sendServerRequest(url, data, callback)
{
    fetch(url, {
        method: "POST",
        headers: RequestHeaders.jsonWithToken(),
        body: JSON.stringify(data)
    })
        .then(response => response.json())
        .then(data => callback(data));
}
