import {NotifyInterface} from "../../../web/ts/framework/Notification";
import {HttpRequestInterface, RequestHeadersInterface} from "../../../web/ts/types";
import AuthStrategyFactory from "../../../web/ts/user/AuthStrategyFactory";
import LoginController, {AuthStrategy} from "../../../web/ts/user/LoginController";
import {LoginView} from "../../../web/ts/user/LoginView";

describe("Login Controller", () => {
    let mockView: jest.Mocked<LoginView>;
    let mockHttpRequest: jest.Mocked<HttpRequestInterface>;
    let mockRequestHeaders: jest.Mocked<RequestHeadersInterface>;
    let mockNotify: jest.Mocked<NotifyInterface>;
    let mockStrategyFactory: jest.Mocked<AuthStrategyFactory>;
    let mockEmailStrategy: jest.Mocked<AuthStrategy>;
    let mockOAuthStrategy: jest.Mocked<AuthStrategy>;

    let loginButtonHandler: () => void;
    let formSubmitHandler: (event: SubmitEvent) => void;
    let oAuthHandlers: Map<string, () => void>;

    beforeEach(() => {
        oAuthHandlers = new Map();

        mockView = {
            bindLoginButtonClickHandler: jest.fn((handler) => {
                loginButtonHandler = handler;
            }),
            bindFormSubmitHandler: jest.fn((handler) =>  {
                formSubmitHandler = handler;
            }),
            bindOAuthHandler: jest.fn((provider, handler) => {
                oAuthHandlers.set(provider, handler);
            }),
            bindModalCloseHandler: jest.fn(),
            showModal: jest.fn(),
            hideModal: jest.fn(),
            getAvailableAuthProviders: jest.fn().mockReturnValue(["google", "github"]),
            getTask: jest.fn(),
            showPhoneCodeRow: jest.fn(),
            hidePhoneCodeRow: jest.fn(),
            setLoggedInState: jest.fn(),
            setLoggedOutState: jest.fn(),
            getLoginForm: jest.fn(),
            getLoginButton: jest.fn(),
            emailInput: jest.fn(),
            phoneCodeInput: jest.fn(),
        } as unknown as jest.Mocked<LoginView>;

        mockHttpRequest = {
            query: jest.fn(),
        } as jest.Mocked<HttpRequestInterface>;

        mockRequestHeaders = {
            html: jest.fn(),
            json: jest.fn(),
            jsonWithToken: jest.fn().mockReturnValue({"Authorisation":"Bearer token"}),
        } as jest.Mocked<RequestHeadersInterface>;

        mockNotify = {
            success: jest.fn(),
            error: jest.fn(),
            warning: jest.fn(),
            info: jest.fn(),
        } as jest.Mocked<NotifyInterface>;

        mockEmailStrategy = {
            getName: jest.fn().mockReturnValue("email"),
            authenticate: jest.fn().mockResolvedValue({
                username: "test@example.com",
                message: "Login successful"
            }),
        } as jest.Mocked<AuthStrategy>;

        mockOAuthStrategy = {
            getName: jest.fn().mockReturnValue("oauth-google"),
            authenticate: jest.fn().mockResolvedValue({
                username: "oauth_user",
                message: "OAuth login successful"
            }),
        } as jest.Mocked<AuthStrategy>;

        mockStrategyFactory = {
            createEmailStrategy: jest.fn().mockReturnValue(mockEmailStrategy),
            createOAuthStrategy: jest.fn().mockReturnValue(mockOAuthStrategy),
            httpRequest: mockHttpRequest,
            requestHeaders: mockRequestHeaders,
            notify: mockNotify,
        } as unknown as jest.Mocked<AuthStrategyFactory>;

        new LoginController(
            mockView,
            mockHttpRequest,
            mockRequestHeaders,
            mockNotify,
            mockStrategyFactory,
        );
    });

    afterEach(() => jest.clearAllMocks());

    describe("Initialisation", () => {
        it("should bind all event handlers on construction", () => {
            expect(mockView.bindLoginButtonClickHandler).toHaveBeenCalledTimes(1);
            expect(mockView.bindFormSubmitHandler).toHaveBeenCalledTimes(1);
            expect(mockView.bindModalCloseHandler).toHaveBeenCalledTimes(1);
            expect(mockView.getAvailableAuthProviders).toHaveBeenCalledTimes(1);
        });

        it("should bind OAuth handlers for all available providers", () => {
            expect(mockView.bindOAuthHandler).toHaveBeenCalledWith("google", expect.any(Function));
            expect(mockView.bindOAuthHandler).toHaveBeenCalledWith("github", expect.any(Function));
            expect(mockView.bindOAuthHandler).toHaveBeenCalledTimes(2);
        });
    });

    describe("loginButtonHandler", () => {
        it("should show the modal when the login button is clicked", () => {
            loginButtonHandler();
            expect(mockView.showModal).toHaveBeenCalledTimes(1);
        });
    });

    describe("OAuth Authentication", () => {
        it("should create OAuth strategy and authenticate when OAuth handler is triggered", async () => {
            const googleHandler = oAuthHandlers.get("google");

            await googleHandler!();

            expect(mockStrategyFactory.createOAuthStrategy).toHaveBeenCalledWith("google");
            expect(mockOAuthStrategy.authenticate).toHaveBeenCalledWith(undefined);
        });

        it("should hide modal and show success notification on successful OAuth login", async () => {
            const googleHandler = oAuthHandlers.get("google");

            await googleHandler!();
            await new Promise(resolve => setTimeout(resolve, 0));

            expect(mockView.hideModal).toHaveBeenCalled();
            expect(mockNotify.success).toHaveBeenCalledWith("OAuth login successful");
            expect(mockView.setLoggedInState).toHaveBeenCalledWith("oauth_user");
        });
    });

    describe("Email Authentication", () => {
        it("should create email strategy when form is submitted for login", () => {
            (mockView.getTask as jest.Mock).mockReturnValue("login");
            (mockView.getLoginForm as jest.Mock).mockReturnValue(document.createElement("form"));

            const mockEvent = {
                preventDefault: jest.fn()
            } as unknown as SubmitEvent;

            formSubmitHandler(mockEvent);

            expect(mockEvent.preventDefault).toHaveBeenCalled();
            expect(mockStrategyFactory.createEmailStrategy).toHaveBeenCalledWith(
                expect.any(Function)
            );
        });

        it("should pass phone code callback to email strategy", () => {
            (mockView.getTask as jest.Mock).mockReturnValue("login");
            (mockView.getLoginForm as jest.Mock).mockReturnValue(document.createElement("form"));

            const mockEvent = {
                preventDefault: jest.fn()
            } as unknown as SubmitEvent;

            formSubmitHandler(mockEvent);

            const callbackArg = (mockStrategyFactory.createEmailStrategy as jest.Mock).mock.calls[0][0];
            callbackArg();

            expect(mockView.showPhoneCodeRow).toHaveBeenCalled();
        });

        it("should authenticate with form data for email strategy", async () => {
            (mockView.getTask as jest.Mock).mockReturnValue("login");

            const mockForm = document.createElement("form");
            const emailInput = document.createElement("input");
            emailInput.name = "email";
            emailInput.value = "test@example.com";
            mockForm.appendChild(emailInput);

            (mockView.getLoginForm as jest.Mock).mockReturnValue(mockForm);

            const mockEvent = {
                preventDefault: jest.fn()
            } as unknown as SubmitEvent;

            formSubmitHandler(mockEvent);
            await new Promise(resolve => setTimeout(resolve, 0));

            expect(mockEmailStrategy.authenticate).toHaveBeenCalled();
            expect(mockView.hideModal).toHaveBeenCalled();
            expect(mockNotify.success).toHaveBeenCalledWith("Login successful");
        });
    });

    describe("Logout", () => {
        it("should call logout endpoint when task is not login", async () => {
            (mockView.getTask as jest.Mock).mockReturnValue("logout");
            (mockHttpRequest.query as jest.Mock).mockResolvedValue({
                message: "Logged out successfully"
            });

            const mockEvent = {
                preventDefault: jest.fn()
            } as unknown as SubmitEvent;

            formSubmitHandler(mockEvent);
            await new Promise(resolve => setTimeout(resolve, 0));

            expect(mockHttpRequest.query).toHaveBeenCalledWith("/logout", {
                method: "POST",
                headers: {"Authorisation":"Bearer token"},
            });
        });

        it("should show success notification and update view on successful logout", async () => {
            (mockView.getTask as jest.Mock).mockReturnValue("logout");
            (mockHttpRequest.query as jest.Mock).mockResolvedValue({
                message: "Logged out successfully"
            });

            const mockEvent = {
                preventDefault: jest.fn()
            } as unknown as SubmitEvent;

            formSubmitHandler(mockEvent);
            await new Promise(resolve => setTimeout(resolve, 0));

            expect(mockNotify.success).toHaveBeenCalledWith("Logged out successfully");
            expect(mockView.setLoggedOutState).toHaveBeenCalled();
        });

        it("should not update view if logout response is invalid", async () => {
            (mockView.getTask as jest.Mock).mockReturnValue("logout");
            (mockHttpRequest.query as jest.Mock).mockResolvedValue({
                invalidResponse: true
            });

            const mockEvent = {
                preventDefault: jest.fn()
            } as unknown as SubmitEvent;

            formSubmitHandler(mockEvent);
            await new Promise(resolve => setTimeout(resolve, 0));

            expect(mockNotify.success).not.toHaveBeenCalled();
            expect(mockView.setLoggedOutState).not.toHaveBeenCalled();
        });
    });

    describe("Error Handling", () => {
        it("should handle authentication errors gracefully", async () => {
            const consoleErrorSpy = jest.spyOn(console, "error").mockImplementation();
            (mockEmailStrategy.authenticate as jest.Mock).mockRejectedValue(new Error("Auth failed"));
            (mockView.getTask as jest.Mock).mockReturnValue("login");
            (mockView.getLoginForm as jest.Mock).mockReturnValue(document.createElement("form"));

            const mockEvent = {
                preventDefault: jest.fn()
            } as unknown as SubmitEvent;

            formSubmitHandler(mockEvent);
            await new Promise(resolve => setTimeout(resolve, 0));

            expect(consoleErrorSpy).toHaveBeenCalled();
            expect(mockView.hideModal).not.toHaveBeenCalled();

            consoleErrorSpy.mockRestore();
        });

        it("should not proceed if strategy returns null", async () => {
            (mockOAuthStrategy.authenticate as jest.Mock).mockResolvedValue(null);

            const googleHandler = oAuthHandlers.get("google");
            await googleHandler!();
            await new Promise(resolve => setTimeout(resolve, 0));

            expect(mockView.hideModal).not.toHaveBeenCalled();
            expect(mockNotify.success).not.toHaveBeenCalled();
        });
    });
});
