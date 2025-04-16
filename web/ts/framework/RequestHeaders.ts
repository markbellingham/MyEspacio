import {RequestHeadersInterface} from "../types";

class RequestHeaders implements RequestHeadersInterface
{
    private getLayoutToken(): string {
        const tokenElement = document.querySelector<HTMLInputElement>("#layout-token");
        return tokenElement?.value || "";
    }

    html(): Headers {
        const headers = new Headers();
        headers.append("X-Layout", this.getLayoutToken());
        return headers;
    }

    json(): Headers {
        const headers = new Headers();
        headers.append("Accept", "application/json");
        return headers;
    }

    jsonWithToken(): Headers {
        const headers = new Headers();
        headers.append("X-Layout", this.getLayoutToken());
        headers.append("Accept", "application/json");
        return headers;
    }
}

const requestHeaders = new RequestHeaders();
export default requestHeaders;
