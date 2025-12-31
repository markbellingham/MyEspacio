import {NotifyInterface} from "../framework/Notification";
import {HttpRequestInterface, RequestHeadersInterface} from "../types";
import {AuthStrategy, LoginResponse} from "./LoginController";

export class EmailAuthStrategy implements AuthStrategy
{
    constructor(
        private HttpRequest: HttpRequestInterface,
        private requestHeaders: RequestHeadersInterface,
        private notify: NotifyInterface,
        private onPhoneCodeRequired: () => void,
    ) {
    }

    getName(): string {
        return "email";
    }

    async authenticate(data: FormData): Promise<LoginResponse | null> {
        const email = data.get("email") as string;
        const phoneCode = data.get("phone_code") as string;

        if (email === "") {
            throw new Error("Email is required.");
        }

        const response = await this.HttpRequest.query("/login", {
            method: "POST",
            body: data,
            headers: this.requestHeaders.jsonWithToken(),
        }) as LoginResponse;

        if (phoneCode === "") {
            this.onPhoneCodeRequired();
            this.notify.success(response.message);
            return null;
        }

        return response;
    }
}
