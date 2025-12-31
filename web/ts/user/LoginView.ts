export class LoginView {
    private oauthButtons: Map<string, HTMLButtonElement> = new Map();
    private modalCloseButton: NodeListOf<HTMLButtonElement>;

    constructor(
        private loginButton: HTMLButtonElement,
        private loginModal: HTMLDivElement,
        private loginForm: HTMLFormElement,
    ) {
        const oauthButtonElements = this.loginModal.querySelectorAll("[data-auth-selector]");
        oauthButtonElements.forEach(button => {
            const provider = (button as HTMLButtonElement)
                .getAttribute("data-auth-method")
                ?.replace("oauth-", "");
            if (provider) {
                this.oauthButtons.set(provider, button as HTMLButtonElement);
            }
        });
        this.modalCloseButton = this.loginModal.querySelectorAll("[data-dismiss=\"modal\"]");
    }

    showModal()
    {
        this.loginModal.classList.add("active");
        this.emailInput().focus();
    }

    hideModal()
    {
        this.loginModal.classList.remove("active");
        this.loginForm.reset();
        this.hidePhoneCodeRow();
    }

    setLoggedInState(username: string)
    {
        this.loginButton.textContent = `Log Out ${username}`;
        this.loginButton.setAttribute("data-task", "logout");
    }

    setLoggedOutState()
    {
        this.loginButton.textContent = "Log In";
        this.loginButton.setAttribute("data-task", "login");
    }

    /** */
    getLoginButton(): HTMLButtonElement
    {
        return this.loginButton;
    }

    getLoginForm(): HTMLFormElement
    {
        return this.loginForm;
    }

    getTask(): string
    {
        return this.loginButton.getAttribute("data-task") ?? "";
    }

    emailInput(): HTMLInputElement
    {
        return this.loginForm.elements.namedItem("email") as HTMLInputElement;
    }

    phoneCodeInput(): HTMLInputElement
    {
        return this.loginForm.elements.namedItem("phone_code") as HTMLInputElement;
    }

    showPhoneCodeRow()
    {
        this.phoneCodeInput().closest("[data-phone-code-row]")?.removeAttribute("hidden");
        this.phoneCodeInput().focus();
    }

    hidePhoneCodeRow()
    {
        this.phoneCodeInput().closest("div")?.setAttribute("hidden", "");
    }

    bindLoginButtonClickHandler(handler: () => void)
    {
        this.loginButton.addEventListener("click", handler);
    }

    bindOAuthHandler(provider: string,  handler: () => void)
    {
        const button = this.oauthButtons.get(provider);
        if (button) {
            button.addEventListener("click", handler);
        } else {
            console.warn(`No OAuth button found for provider ${provider}`);
        }
    }

    bindFormSubmitHandler(handler: (event: SubmitEvent) => void)
    {
        this.loginForm.addEventListener("submit", handler);
    }

    bindModalCloseHandler()
    {
        this.modalCloseButton.forEach(button => {
            button.addEventListener("click", this.hideModal.bind(this));
        });
    }

    getAvailableAuthProviders(): string[]
    {
        return Array.from(this.oauthButtons.keys());
    }
}
