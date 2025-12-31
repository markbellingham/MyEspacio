import {HttpRequestInterface, RequestHeadersInterface} from "../../../web/ts/types";
import {OAuthStrategy} from "../../../web/ts/user/OAuthStrategy";

describe("OAuthStrategy", () => {
    let httpRequest: jest.Mocked<HttpRequestInterface>;
    let requestHeaders: jest.Mocked<RequestHeadersInterface>;
    let oAuthStrategy: OAuthStrategy;

    beforeEach(() => {
        httpRequest = {
            query: jest.fn(),
        } as jest.Mocked<HttpRequestInterface>;

        requestHeaders = {
            jsonWithToken: jest.fn().mockReturnValue({"Content-Type": "application/json"}),
            json: jest.fn(),
            html: jest.fn(),
        } as jest.Mocked<RequestHeadersInterface>;

        Object.defineProperty(window, "location", {
            writable: true,
            value: {href: ""}
        });
    });

    afterEach(() => jest.clearAllMocks());

    describe("Get name", () => {
        it("returns the name of the google strategy", () => {
            oAuthStrategy = new OAuthStrategy(
                "google",
                httpRequest,
                requestHeaders,
            );
            expect(oAuthStrategy.getName()).toBe("oauth-google");
        });
        it("returns the name of the github strategy", () => {
            oAuthStrategy = new OAuthStrategy(
                "github",
                httpRequest,
                requestHeaders,
            );
            expect(oAuthStrategy.getName()).toBe("oauth-github");
        });
    });

    describe("authenticate", () => {
        it("should redirect to the google oauth provider URL", async () => {
            oAuthStrategy = new OAuthStrategy(
                "google",
                httpRequest,
                requestHeaders
            );
            const result = oAuthStrategy.authenticate();
            expect(window.location.href).toBe("/auth/google");
            expect(result).toBeNull();
        });
        it("should redirect to the github oauth provider URL", async () => {
            oAuthStrategy = new OAuthStrategy(
                "github",
                httpRequest,
                requestHeaders
            );
            const result = oAuthStrategy.authenticate();
            expect(window.location.href).toBe("/auth/github");
            expect(result).toBeNull();
        });
    });

    describe("handleCallback", () => {
        const mockCode = "auth_code_123";
        const mockLoginResponse = {
            token: "jwt_token_456",
            user: {
                id: "user123",
                email: "user@example.tld"
            },
        };

        it("should make a POST request to the google callback endpoint.", async () => {
            oAuthStrategy = new OAuthStrategy(
                "google",
                httpRequest,
                requestHeaders,
            );

            await oAuthStrategy.handleCallback(mockCode);

            expect(httpRequest.query).toHaveBeenCalledWith(
                "/auth/callback/google",
                expect.objectContaining({
                    method: "POST"
                })
            );
        });

        it("should make a POST request to the github callback endpoint", async () => {
            oAuthStrategy = new OAuthStrategy(
                "github",
                httpRequest,
                requestHeaders,
            );

            await oAuthStrategy.handleCallback(mockCode);

            expect(httpRequest.query).toHaveBeenCalledWith(
                "/auth/callback/github",
                expect.objectContaining({
                    method: "POST"
                })
            );
        });

        it("should include the authorisation code and google provider in the request body", async () => {
            oAuthStrategy = new OAuthStrategy(
                "google",
                httpRequest,
                requestHeaders,
            );

            httpRequest.query.mockResolvedValue(mockLoginResponse);

            await oAuthStrategy.handleCallback(mockCode);

            expect(httpRequest.query).toHaveBeenCalledWith(
                "/auth/callback/google",
                expect.objectContaining({
                    body: JSON.stringify({code: mockCode, provider: "google"})
                })
            );
        });

        it("should include the authorisation code and github provider in the request body", async () => {
            oAuthStrategy = new OAuthStrategy(
                "github",
                httpRequest,
                requestHeaders,
            );

            httpRequest.query.mockResolvedValue(mockLoginResponse);

            await oAuthStrategy.handleCallback(mockCode);

            expect(httpRequest.query).toHaveBeenCalledWith(
                "/auth/callback/github",
                expect.objectContaining({
                    body: JSON.stringify({code: mockCode, provider: "github"})
                })
            );
        });

        it("should include proper headerswith JSON and token", async () => {
            oAuthStrategy = new OAuthStrategy(
                "github",
                httpRequest,
                requestHeaders,
            );

            httpRequest.query.mockResolvedValue(mockLoginResponse);

            await oAuthStrategy.handleCallback(mockCode);

            expect(requestHeaders.jsonWithToken).toHaveBeenCalled();
            expect(httpRequest.query).toHaveBeenCalledWith(
                "/auth/callback/github",
                expect.objectContaining({
                    headers: {
                        "Content-Type": "application/json",
                    }
                })
            );
        });

        it("should return the login response on success", async () => {
            oAuthStrategy = new OAuthStrategy(
                "github",
                httpRequest,
                requestHeaders,
            );

            httpRequest.query.mockResolvedValue(mockLoginResponse);

            const result = await oAuthStrategy.handleCallback(mockCode);
            expect(result).toEqual(mockLoginResponse);
        });
    });
});
