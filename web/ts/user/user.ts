import {httpRequest} from "../framework/HttpRequest";
import {notify} from "../framework/Notification";
import requestHeaders from "../framework/RequestHeaders";
import AuthStrategyFactory from "./AuthStrategyFactory";
import LoginController from "./LoginController";
import {LoginView} from "./LoginView";

document.addEventListener("DOMContentLoaded", () => {

    const loginButton = document.querySelector("#login-btn") as HTMLButtonElement;
    const loginModal = document.querySelector("#login-modal") as HTMLDivElement;
    const loginForm = document.querySelector("#login-form") as HTMLFormElement;

    if (!loginButton || !loginModal || !loginForm) {
        console.error(loginButton, loginModal, loginForm);
        console.error("Login: Required elements not found in DOM.");
        return;
    }

    const strategyFactory = new AuthStrategyFactory(
        httpRequest,
        requestHeaders,
        notify,
    );
    new LoginController(
        new LoginView(
            loginButton,
            loginModal,
            loginForm
        ),
        httpRequest,
        requestHeaders,
        notify,
        strategyFactory
    );
});
