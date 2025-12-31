import {HttpRequestInterface, RequestHeadersInterface} from "../types";
import {AuthStrategy, LoginResponse} from "./LoginController";

export class OAuthStrategy implements AuthStrategy
{
    constructor(
        private provider: string,
        private httpRequest: HttpRequestInterface,
        private requestHeaders: RequestHeadersInterface,
    ) {
    }

    getName(): string {
        return "oauth-" + this.provider;
    }

    authenticate(): null {
        window.location.href = "/auth/" + this.provider;

        return null;
    }

    async handleCallback(code: string): Promise<LoginResponse | null> {
        return await this.httpRequest.query("/auth/callback/" + this.provider, {
            method: "POST",
            headers: this.requestHeaders.jsonWithToken(),
            body: JSON.stringify({code, provider: this.provider}),
        }) as LoginResponse;
    }
}
