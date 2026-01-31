import {HttpRequestInterface} from "../types";
import {Notification, notify} from "./Notification";

export class HttpRequest implements HttpRequestInterface {

    constructor(
        private readonly notify: Notification
    ) {
    }

    public async query(
        url: string,
        options: RequestInit = {}
    ): Promise<unknown> {
        try {
            const response = await fetch(url, options);
            const contentType = response.headers.get("Content-Type") || "";
            const status = response.status;

            let data: unknown;
            if (contentType.includes("application/json")) {
                data = await response.json();
            } else if (contentType.includes("text")) {
                data = await response.text();
            } else if (
                contentType.includes("application/octet-stream") ||
                contentType.includes("image") ||
                contentType.includes("video")
            ) {
                data = await response.blob();
            } else {
                data = response.body;
            }

            if (status >= 400) {
                const errorMessage =
                    typeof data === "object" &&
                    data !== null &&
                    "message" in data
                        ? (data as { message: string }).message
                        : `Error ${status}`;
                throw new Error(errorMessage);
            }

            return data;
        } catch (error: unknown) {
            if (error instanceof Error && error.name === "AbortError") {
                throw error;
            }
            const message = error instanceof Error
                    ? error.message
                    : "An unknown error occurred";
            this.notify.error(`Request failed: ${message}`);
            throw new Error(message);
        }
    }
}

export const httpRequest = new HttpRequest(notify);
