import {Notify} from "./Notify";

export class HttpRequest {
    async fetchWithStatus(url, options = {}) {
        try {
            const response = await fetch(url, options);
            const status = response.status;
            const contentType = response.headers.get("Content-Type") || "";

            let data;
            if (contentType.includes("application/json")) {
                data = await response.json();
            } else if (contentType.includes("text")) {
                data = await response.text();
            } else if (contentType.includes("application/octet-stream") || contentType.includes("image") || contentType.includes("video")) {
                data = await response.blob();
            } else {
                data = response.body;
            }

            return { data, status };
        } catch (error) {
            new Notify("error", `Request failed: ${error.message}`);
            throw error;
        }
    }

    async handleResponse(url, options = {}) {
        const { data, status } = await this.fetchWithStatus(url, options);

        if (status >= 400) {
            new Notify("error", `Request failed: ${data?.message || `Error ${status}`}`);
            throw new Error(data?.message || `Error ${status}`);
        }

        return data;
    }
}
