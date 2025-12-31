import {NotifyInterface} from "../../../web/ts/framework/Notification";
import {HttpRequestInterface, RequestHeadersInterface} from "../../../web/ts/types";
import AuthStrategyFactory from "../../../web/ts/user/AuthStrategyFactory";
import {EmailAuthStrategy} from "../../../web/ts/user/EmailAuthStrategy";
import {OAuthStrategy} from "../../../web/ts/user/OAuthStrategy";

describe("AuthStrategyFactory", () => {
    let mockHttpRequest: HttpRequestInterface;
    let mockRequestHeaders: RequestHeadersInterface;
    let mockNotify: NotifyInterface;
    let factory: AuthStrategyFactory;

    beforeEach(() => {
        mockHttpRequest = {
            query: jest.fn(),
        } as jest.Mocked<HttpRequestInterface>;
        mockRequestHeaders = {
            html: jest.fn(),
            json: jest.fn(),
            jsonWithToken: jest.fn(),
        } as jest.Mocked<RequestHeadersInterface>;
        mockNotify = {
            success: jest.fn(),
            error: jest.fn(),
            warning: jest.fn(),
            info: jest.fn(),
        } as jest.Mocked<NotifyInterface>;

        factory = new AuthStrategyFactory(
            mockHttpRequest,
            mockRequestHeaders,
            mockNotify,
        );
    });

    afterEach(() => jest.clearAllMocks());

    describe("Email auth strategy", () => {
        it("should create the email strategy", () => {
            const onPhoneCodeRequired = jest.fn();
            const createdStrategy = factory.createEmailStrategy(onPhoneCodeRequired);

            expect(createdStrategy.getName()).toBe("email");
            expect(createdStrategy).toBeInstanceOf(EmailAuthStrategy);
        });

        it("should create a new instance on each call", () => {
            const strategy1 = factory.createEmailStrategy(() => {});
            const strategy2 = factory.createEmailStrategy(() => {});

            expect(strategy1).not.toBe(strategy2);
        });
    });

    describe("OAuth strategy", () => {
        it.each(
            [
                ["google", "oauth-google"],
                ["github", "oauth-github"],
                ["facebook", "oauth-facebook"],
            ]
        )("should create the oauth strategy", (provider, name) => {
            const createdStrategy = factory.createOAuthStrategy(provider);

            expect(createdStrategy.getName()).toBe(name);
            expect(createdStrategy).toBeInstanceOf(OAuthStrategy);
        });

        it("should create a new instance on each call", () => {
            const strategy1 = factory.createOAuthStrategy("google");
            const strategy2 = factory.createOAuthStrategy("github");

            expect(strategy1).not.toBe(strategy2);
        });
    });
});
