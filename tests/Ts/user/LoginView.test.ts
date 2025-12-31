import {LoginView} from "../../../web/ts/user/LoginView";

describe("Login View", () =>{
    let loginButton: HTMLButtonElement;
    let loginModal: HTMLDivElement;
    let loginForm: HTMLFormElement;
    let loginView: LoginView;

    beforeEach(() => {
        document.body.innerHTML = `
        <button id="loginBtn" data-task="login">Log In</button>
        <div id="loginModal" class="modal">
            <button data-dismiss="modal">X</button>
            <form id="loginForm">
                <input type="email" name="email">
                <div data-phone-code-row hidden>
                    <input type="text" name="phone_code">
                </div>
                <button type="button" data-dismiss="modal">Cancel</button>
                <button type="submit">Submit</button>
            </form>
            <button data-auth-selector data-auth-method="google">Google</button>
            <button data-auth-selector data-auth-method="github">Github</button>
        </div>`;
        loginButton = document.getElementById("loginBtn") as HTMLButtonElement;
        loginModal = document.getElementById("loginModal") as HTMLDivElement;
        loginForm = document.getElementById("loginForm") as HTMLFormElement;

        loginView = new LoginView(loginButton, loginModal, loginForm);
    });

    afterEach(() => {
        document.body.innerHTML = "";
    });

    describe("Constructor", () => {
        it("should initialise with the provided elements", () => {
            expect(loginView.getLoginButton()).toBe(loginButton);
            expect(loginView.getLoginForm()).toBe(loginForm);
        });

        it("should find and store OAuth buttons", () => {
            const providers = loginView.getAvailableAuthProviders();
            expect(providers).toHaveLength(2);
            expect(providers).toContain("google");
            expect(providers).toContain("github");
        });

        it("should handle missing OAuth buttons gracefully", () => {
            document.body.innerHTML = `
        <button id="loginBtn" data-task="login">Log In</button>
        <div id="loginModal" class="modal">
            <button data-bs-dismiss="modal">X</button>
            <form id="loginForm">
                <input type="email" name="email">
                <div data-phone-code-row hidden>
                    <input type="text" name="phone_code">
                </div>
                <button type="submit">Submit</button>
            </form>
        </div>`;
            loginButton = document.getElementById("loginBtn") as HTMLButtonElement;
            loginModal = document.getElementById("loginModal") as HTMLDivElement;
            loginForm = document.getElementById("loginForm") as HTMLFormElement;

            const view = new LoginView(loginButton, loginModal, loginForm);
            const providers = view.getAvailableAuthProviders();
            expect(providers).toHaveLength(0);
        });
    });

    describe("Modal visibility", () => {
        it("should show the modal and focus the email input", () => {
            const emailInput = loginView.emailInput();
            const focusSpy = jest.spyOn(emailInput, "focus");

            loginView.showModal();

            expect(loginModal.classList.contains("active")).toBe(true);
            expect(focusSpy).toHaveBeenCalled();
        });

        it("should hide the modal and reset the form", () => {
            loginModal.classList.add("active");
            loginView.emailInput().value = "test@example.tld";

            loginView.hideModal();

            expect(loginModal.classList.contains("active")).toBe(false);
            expect(loginView.emailInput().value).toBe("");
        });

        it("should hide the phone code row when hiding the modal", () => {
            loginView.showPhoneCodeRow();
            loginView.hideModal();

            const phoneCodeRow = loginView.phoneCodeInput().closest("[data-phone-code-row]");
            expect(phoneCodeRow?.hasAttribute("hidden")).toBe(true);
        });
    });

    describe("login state management", () => {
        it("should set logged instate with user name", () => {
            loginView.setLoggedInState("John Doe");
            loginButton = document.getElementById("loginBtn") as HTMLButtonElement;

            expect(loginButton.textContent).toBe("Log Out John Doe");
            expect(loginButton.dataset.task).toBe("logout");
        });

        it("should set logged out state", () => {
            loginButton.textContent = "Log Out John Doe";
            loginButton.dataset.task = "logout";

            loginView.setLoggedOutState();

            expect(loginButton.textContent).toBe("Log In");
            expect(loginButton.dataset.task).toBe("login");
        });

        it("should get current task", () => {
            loginButton.dataset.task = "login";
            expect(loginView.getTask()).toBe("login");

            loginButton.dataset.task = "logout";
            expect(loginView.getTask()).toBe("logout");
        });

        it("should return an empty string if task is not set", () => {
            loginButton.removeAttribute("data-task");
            expect(loginView.getTask()).toBe("");
        });
    });

    describe("phone code row visibility", () => {
        it("should show the phone code row and focus input", () => {
            const phoneCodeInput = loginView.phoneCodeInput();
            const focusSpy = jest.spyOn(phoneCodeInput, "focus");

            loginView.showPhoneCodeRow();

            const phoneCodeRow = phoneCodeInput.closest("[data-phone-code-row]");
            expect(phoneCodeRow?.hasAttribute("hidden")).toBe(false);
            expect(focusSpy).toHaveBeenCalled();
        });

        it("should hide the phone code row", () => {
            loginView.showPhoneCodeRow();
            loginView.hidePhoneCodeRow();

            const phoneCodeRow = loginView.phoneCodeInput().closest("[data-phone-code-row]");
            expect(phoneCodeRow?.hasAttribute("hidden")).toBe(true);
        });
    });

    describe("Event handlers", () => {
        it("should bind login to click handler", () => {
            const handler = jest.fn();
            loginView.bindLoginButtonClickHandler(handler);

            loginButton.click();

            expect(handler).toHaveBeenCalledTimes(1);
        });

        it("should bind oauth handler for valid provider", () => {
            const handler = jest.fn();
            loginView.bindOAuthHandler("google", handler);

            const googleButton = document.querySelector<HTMLButtonElement>("[data-auth-selector][data-auth-method=\"google\"]");
            googleButton?.click();

            expect(handler).toHaveBeenCalledTimes(1);
        });

        it("should warn when binding oauth handler for invalid provider", () => {
            const consoleWarnSpy = jest.spyOn(console, "warn").mockImplementation(() => {});
            const handler = jest.fn();

            loginView.bindOAuthHandler("invalid", handler);
            expect(consoleWarnSpy).toHaveBeenCalledWith("No OAuth button found for provider invalid");
            consoleWarnSpy.mockRestore();
        });

        it("should bind form submit handler", () => {
            const submit = jest.fn((e: Event) => e.preventDefault());
            loginView.bindFormSubmitHandler(submit);

            const event = new Event("submit", {bubbles: true, cancelable: true});
            loginForm.dispatchEvent(event);

            expect(submit).toHaveBeenCalledTimes(1);
        });

        it("should bind modal close handler", () => {
            loginView.bindModalCloseHandler();
            loginView.emailInput().value = "joe.bloggs@example.tld";

            const closeButton = loginModal.querySelector<HTMLButtonElement>("[data-dismiss=\"modal\"]");
            closeButton?.click();

            expect(loginModal.classList.contains("active")).toBe(false);
            expect(loginView.emailInput().value).toBe("");
        });
    });

    describe("Form input getters", () => {
        it("should return email input", () => {
            const emailInput = loginView.emailInput();
            expect(emailInput).toBeInstanceOf(HTMLInputElement);
            expect(emailInput.name).toBe("email");
            expect(emailInput.type).toBe("email");
        });

        it("should return phone code input", () => {
            loginView.showPhoneCodeRow();
            const phoneCodeInput = loginView.phoneCodeInput();
            expect(phoneCodeInput).toBeInstanceOf(HTMLInputElement);
            expect(phoneCodeInput.name).toBe("phone_code");
            expect(phoneCodeInput.type).toBe("text");
        });
    });
});
