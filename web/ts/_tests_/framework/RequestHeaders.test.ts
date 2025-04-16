import requestHeaders from "../../framework/RequestHeaders";

describe("RequestHeaders", () => {
    beforeEach(() => {
        document.body.innerHTML = ""; // Reset DOM between tests
    });

    test("html() includes X-Layout header with token", () => {
        const token = "abc123";
        const input = document.createElement("input");
        input.id = "layout-token";
        input.value = token;
        document.body.appendChild(input);

        const headers = requestHeaders.html();
        expect(headers.get("X-Layout")).toBe(token);
    });

    test("json() includes only Accept: application/json", () => {
        const headers = requestHeaders.json();
        expect(headers.get("Accept")).toBe("application/json");
        expect(headers.has("X-Layout")).toBe(false);
    });

    test("jsonWithToken() includes both Accept and X-Layout headers", () => {
        const token = "xyz789";
        const input = document.createElement("input");
        input.id = "layout-token";
        input.value = token;
        document.body.appendChild(input);

        const headers = requestHeaders.jsonWithToken();
        expect(headers.get("X-Layout")).toBe(token);
        expect(headers.get("Accept")).toBe("application/json");
    });

    test("getLayoutToken() returns empty string if token not found", () => {
        const headers = requestHeaders.html();
        expect(headers.get("X-Layout")).toBe(""); // no #layout-token input
    });
});
