import {NotifyInterface} from "../framework/Notification";
import {HttpRequestInterface, RequestHeadersInterface} from "../types";
import {EmailAuthStrategy} from "./EmailAuthStrategy";
import {OAuthStrategy} from "./OAuthStrategy";

export default class AuthStrategyFactory
{
    constructor(
        private httpRequest: HttpRequestInterface,
        private requestHeaders: RequestHeadersInterface,
        private notify: NotifyInterface,
    ) {
    }

    public createEmailStrategy(onPhoneCodeRequired: () => void): EmailAuthStrategy
    {
        return new EmailAuthStrategy(
            this.httpRequest,
            this.requestHeaders,
            this.notify,
            onPhoneCodeRequired
        );
    }

    public createOAuthStrategy(provider: string): OAuthStrategy
    {
        return new OAuthStrategy(
            provider,
            this.httpRequest,
            this.requestHeaders
        );
    }
}
