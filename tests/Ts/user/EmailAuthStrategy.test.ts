import {NotifyInterface} from "../../../web/ts/framework/Notification";
import {HttpRequestInterface, RequestHeadersInterface} from "../../../web/ts/types";
import {EmailAuthStrategy} from "../../../web/ts/user/EmailAuthStrategy";
import {LoginResponse} from "../../../web/ts/user/LoginController";

describe("EmailAuthStrategy", () => {
    let httpRequest: jest.Mocked<HttpRequestInterface>;
    let requestHeaders: jest.Mocked<RequestHeadersInterface>;
    let notify: jest.Mocked<NotifyInterface>;
    const onPhoneCodeRequired: jest.Mock = jest.fn();
    let emailAuthStrategy: EmailAuthStrategy;

    beforeEach(() => {
        httpRequest = {
            query: jest.fn(),
        } as jest.Mocked<HttpRequestInterface>;

        requestHeaders = {
            jsonWithToken: jest.fn().mockReturnValue({"Content-Type": "application/json"}),
            json: jest.fn(),
            html: jest.fn(),
        } as jest.Mocked<RequestHeadersInterface>;

        notify = {
            success: jest.fn(),
            error: jest.fn(),
            info: jest.fn(),
            warning: jest.fn(),
        } as jest.Mocked<NotifyInterface>;

        emailAuthStrategy = new EmailAuthStrategy(
            httpRequest,
            requestHeaders,
            notify,
            onPhoneCodeRequired,
        );
    });

    afterEach(() => jest.clearAllMocks());

    describe("Get name", () => {
        it("returns the name of the strategy", () => {
            expect(emailAuthStrategy.getName()).toBe("email");
        });
    });

    describe("authenticate", () => {
        let formData: FormData;

        beforeEach(() => formData = new FormData());

        it("should throw an error if the email is not provided", async () => {
            formData.append("email", "");

            await expect(emailAuthStrategy.authenticate(formData)).rejects.toThrow(
                "Email is required."
            );

            expect(httpRequest.query).not.toHaveBeenCalled();
        });

        it("should call onPhoneCodeRequired and notify when phone_code is empty", async () => {
            const mockResponse: LoginResponse = {
                username: "Joe Bloggs",
                message: "Verification code sent to your email.",
            };

            formData.append("email", "test@example.tld");
            formData.append("phone_code", "");

            httpRequest.query.mockResolvedValue(mockResponse);

            const result = await emailAuthStrategy.authenticate(formData);

            expect(httpRequest.query).toHaveBeenCalledWith("/login", {
                method: "POST",
                body: formData,
                headers: {"Content-Type": "application/json"},
            });
            expect(onPhoneCodeRequired).toHaveBeenCalledWith();
            expect(notify.success).toHaveBeenCalledWith(mockResponse.message);
            expect(result).toBeNull();
        });

        it("should return loginResponse when both email and phone_code are provided", async () => {
            const mockResponse: LoginResponse = {
                username: "Joe Bloggs",
                message: "Login successful.",
            };

            formData.append("email", "test@example.tld");
            formData.append("phone_code", "ABC123");

            httpRequest.query.mockResolvedValue(mockResponse);

            const result = await emailAuthStrategy.authenticate(formData);

            expect(httpRequest.query).toHaveBeenCalledWith("/login", {
                method: "POST",
                body: formData,
                headers: {"Content-Type": "application/json"},
            });
            expect(onPhoneCodeRequired).not.toHaveBeenCalled();
            expect(notify.success).not.toHaveBeenCalled();
            expect(result).toEqual(mockResponse);
        });

        it("should use the correct request headers", async () => {
            const customHeaders = new Headers({
                "Content-Type": "application/json",
                "X-Custom-Header": "Bearer ABC123",
            });
            requestHeaders.jsonWithToken.mockReturnValue(customHeaders);

            const mockResponse: LoginResponse = {
                username: "Joe Bloggs",
                message: "Login successful.",
            };

            formData.append("email", "test@example.tld");
            formData.append("phone_code", "ABC123");

            httpRequest.query.mockResolvedValue(mockResponse);

            const result = await emailAuthStrategy.authenticate(formData);

            expect(requestHeaders.jsonWithToken).toHaveBeenCalled();
            expect(httpRequest.query).toHaveBeenCalledWith("/login", {
                method: "POST",
                body: formData,
                headers: customHeaders,
            });
            expect(result).toEqual(mockResponse);
            expect(onPhoneCodeRequired).not.toHaveBeenCalled();
            expect(notify.success).not.toHaveBeenCalled();
        });
    });
});
