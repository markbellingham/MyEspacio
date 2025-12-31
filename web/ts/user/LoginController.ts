import {NotifyInterface} from "../framework/Notification";
import {HttpRequestInterface, RequestHeadersInterface} from "../types";
import AuthStrategyFactory from "./AuthStrategyFactory";
import {LoginView} from "./LoginView";

export interface AuthStrategy {
    authenticate(data?: FormData): Promise<LoginResponse | null> | null;
    getName(): string;
}

export interface LoginResponse {
    username: string;
    message: string;
}

interface LoginFormData {
    email: string;
    phoneCode: string;
    formData: FormData;
}

export default class LoginController {
    private currentStrategy: AuthStrategy | null = null;

    constructor(
        private view: LoginView,
        private httpRequest: HttpRequestInterface,
        private requestHeaders: RequestHeadersInterface,
        private notify: NotifyInterface,
        private strategyFactory: AuthStrategyFactory,
    ) {
        this.events();
    }

    private events(): void
    {
        this.view.bindLoginButtonClickHandler(() => this.view.showModal());

        const providers = this.view.getAvailableAuthProviders();
        providers.forEach(provider => {
            this.view.bindOAuthHandler(provider, () => {
                this.currentStrategy = this.strategyFactory.createOAuthStrategy(provider);
                this.handleAuthentication();
            });
        });

        this.view.bindFormSubmitHandler(event => {
            event.preventDefault();
            const task = this.view.getTask();
            if (task === "login") {
                this.currentStrategy = this.strategyFactory.createEmailStrategy(
                    () => this.view.showPhoneCodeRow()
                );
                this.handleAuthentication();
            } else {
                this.handleLogout();
            }
        });
        this.view.bindModalCloseHandler();
    }

    private async handleAuthentication(): Promise<void>
    {
        if (! this.currentStrategy) {
            return;
        }
         try {
            const data = this.currentStrategy.getName() === "email"
                ? this.getFormData().formData
                : undefined;

            const response = await this.currentStrategy.authenticate(data);

            if (!response) {
                return;
            }

            this.view.hideModal();
            this.notify.success(response.message);
            this.view.setLoggedInState(response.username);
         } catch (error) {
            console.error(error);
         }
    }

    private handleLogout(): void
    {
        this.httpRequest.query("/logout", {
            method: "POST",
            headers: this.requestHeaders.jsonWithToken(),
        })
            .then((response: unknown) => {
                if (! this.isLoginResponse(response)) {
                    return;
                }

                this.notify.success(response.message);
                this.view.setLoggedOutState();
            });
    }

    private isLoginResponse(response: unknown): response is LoginResponse
    {
        return (
            response !== null &&
            typeof response === "object" &&
            "message" in response &&
            typeof response.message === "string"
        );
    }

    private getFormData(): LoginFormData
    {
        const formData = new FormData(this.view.getLoginForm());
        return {
            email: (formData.get("email") ?? "") as string,
            phoneCode: (formData.get("phone_code") ?? "") as string,
            formData: formData,
        };
    }
}
