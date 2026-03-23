import {NotifyInterface} from "../../../web/ts/framework/Notification";
import {PhotoFavePersistence} from "../../../web/ts/pictures/PhotoFavePersistence";
import {HttpRequestInterface, RequestHeadersInterface} from "../../../web/ts/types";
import AuthState from "../../../web/ts/user/AuthState";

describe("PhotoFavePersistence", () => {
    let mockHttpRequest: jest.Mocked<HttpRequestInterface>;
    let mockRequestHeaders: jest.Mocked<RequestHeadersInterface>;
    let mockNotify: jest.Mocked<NotifyInterface>;
    let authState: AuthState;
    let persistence: PhotoFavePersistence;

    beforeEach(() => {
        mockHttpRequest = {
            query: jest.fn().mockResolvedValue({message: "Success!"}),
        } as jest.Mocked<HttpRequestInterface>;
        mockRequestHeaders = {
            json: jest.fn(),
            html: jest.fn(),
            jsonWithToken: jest.fn().mockReturnValue({"Content-Type":"application/json"}),
        } as jest.Mocked<RequestHeadersInterface>;
        mockNotify = {
            success: jest.fn(),
            error: jest.fn(),
            warning: jest.fn(),
            info: jest.fn(),
        } as jest.Mocked<NotifyInterface>;
        authState = {
            isLoggedIn: jest.fn().mockReturnValue(true),
            setLoggedIn: jest.fn(),
            setLoggedOut: jest.fn(),
            getUsername: jest.fn(),
        } as unknown as AuthState;

        persistence = new PhotoFavePersistence(
            mockHttpRequest,
            mockRequestHeaders,
            mockNotify,
            authState,
        );
    });

    it("should save locally when faving", async () => {
        await persistence.save("uuid-1", true, new AbortController().signal);

        expect(persistence.isFaved("uuid-1")).toBe(true);
    });

    it("should remove locally when unfaving", async () => {
        await persistence.save("uuid-1", true, new AbortController().signal);
        await persistence.save("uuid-1", false, new AbortController().signal);

        expect(persistence.isFaved("uuid-1")).toBe(false);
    });

    it("should send POST request when faving", async () => {
        await persistence.save("uuid-1", true, new AbortController().signal);

        expect(mockHttpRequest.query).toHaveBeenCalledWith(
            "/photos/uuid-1/fave",
            expect.objectContaining({
                method: "POST",
                headers: {"Content-Type":"application/json"},
                signal: expect.any(AbortSignal),
            })
        );
    });

    it("should send DELETE request when unfaving", async () => {
        await persistence.save("uuid-1", false, new AbortController().signal);

        expect(mockHttpRequest.query).toHaveBeenCalledWith(
            "/photos/uuid-1/fave",
            expect.objectContaining({
                method: "DELETE",
                headers: {"Content-Type":"application/json"},
                signal: expect.any(AbortSignal),
            })
        );
    });

    it("should return empty set when localStorage contains invalid JSON", () => {
        window.localStorage.setItem("favedPhotos", "{not-json}");
        const result = persistence.load();
        expect(result).toEqual(new Set());
    });

    it("should return empty set when localStorage contains non-array JSON", () => {
        window.localStorage.setItem("favedPhotos", JSON.stringify({foo:"bar"}));
        const result = persistence.load();
        expect(result).toEqual(new Set());
    });

    it("should not call remote request when logged out", async () => {
        (authState.isLoggedIn as jest.Mock).mockReturnValue(false);
        await persistence.save("uuid-1", true, new AbortController().signal);
        expect(mockHttpRequest.query).not.toHaveBeenCalled();
    });

    it("should ignore AbortError from remote request", async () => {
        const abortError = new Error("Abort");
        abortError.name = "AbortError";
        mockHttpRequest.query.mockRejectedValueOnce(abortError);

        await expect(persistence.save("uuid-1", true, new AbortController().signal)).resolves.toBeUndefined();
    });
});
