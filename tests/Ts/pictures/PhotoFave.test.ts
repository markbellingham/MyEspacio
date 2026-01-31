import {NotifyInterface} from "../../../web/ts/framework/Notification";
import {PhotoFave} from "../../../web/ts/pictures/PhotoFave";
import {HttpRequestInterface, RequestHeadersInterface} from "../../../web/ts/types";

describe("PhotoFave", () => {
    let photoContainer: HTMLDivElement;
    let mockHttpRequest: jest.Mocked<HttpRequestInterface>;
    let mockRequestHeaders: jest.Mocked<RequestHeadersInterface>;
    let mockNotify: jest.Mocked<NotifyInterface>;
    // let photoFave: PhotoFave;

    beforeEach(() => {
        photoContainer = document.createElement("div");
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

        jest.useFakeTimers();

        new PhotoFave(
            photoContainer,
            mockHttpRequest,
            mockRequestHeaders,
            mockNotify
        );
    });

    afterEach(() => {
        document.body.innerHTML = "";
        jest.clearAllTimers();
        jest.clearAllMocks();
        jest.useRealTimers();
    });

    describe("Initialisation", () => {
        it("should initialise with event listeners added", () => {
            const addEventListenerSpy = jest.spyOn(photoContainer, "addEventListener");

            new PhotoFave(
                photoContainer,
                mockHttpRequest,
                mockRequestHeaders,
                mockNotify,
            );

            expect(addEventListenerSpy).toHaveBeenCalledTimes(2);
            expect(addEventListenerSpy).toHaveBeenCalledWith("click", expect.any(Function));
            expect(addEventListenerSpy).toHaveBeenCalledWith("photoLoaded", expect.any(Function));
        });
    });

    describe("photo loaded event", () => {
        it("should set the heart button when the photoLoaded event is fired", () => {
            const heartButton = document.createElement("i");
            heartButton.className = "photo-fave";
            heartButton.dataset.uuid = "test-uuid-123";
            photoContainer.appendChild(heartButton);

            const event = new CustomEvent("photoLoaded", {
                bubbles: true,
                detail: {},
            });
            Object.defineProperty(event, "target", {value: heartButton, enumerable: true});

            photoContainer.dispatchEvent(event);

            heartButton.click();
            jest.advanceTimersByTime(500);

            expect(mockHttpRequest.query).toHaveBeenCalled();
        });
    });

    describe("click handling", () => {
        let heartButton: HTMLElement;

        beforeEach(() => {
            heartButton = document.createElement("i");
            heartButton.className = "photo-fave";
            heartButton.dataset.uuid = "test-uuid-123";
            photoContainer.appendChild(heartButton);
        });

        it("should toggle the fave class when clicked", () => {
            expect(heartButton.classList.contains("faved")).toBe(false);

            heartButton.click();

            expect(heartButton.classList.contains("faved")).toBe(true);
        });

        it("should toggle from faved to unfaved", () => {
            heartButton.classList.add("faved");

            heartButton.click();

            expect(heartButton.classList.contains("faved")).toBe(false);
        });

        it("should not process click if the element is not a heart button", () => {
            const otherButton = document.createElement("button");
            photoContainer.appendChild(otherButton);

            otherButton.click();

            expect(heartButton.classList.contains("faved")).toBe(false);
        });

        it("should work with nested elements using event delegation", () => {
            const icon = document.createElement("i");
            heartButton.append(icon);

            icon.click();

            expect(heartButton.classList.contains("faved")).toBe(true);
        });
    });

    describe("Click handling with missing heart button", () => {
        it("should fail gracefully if the heart button is missing or invalid", () => {
            const heartButton = document.createElement("i");
            heartButton.dataset.uuid = "test-uuid-123";
            photoContainer.appendChild(heartButton);

            heartButton.click();

            expect(heartButton.classList.contains("faved")).toBe(false);
        });
    });

    describe("debouncing", () => {
        let heartButton: HTMLElement;

        beforeEach(() => {
            heartButton = document.createElement("i");
            heartButton.className = "photo-fave";
            heartButton.dataset.uuid = "test-uuid-123";
            photoContainer.appendChild(heartButton);
        });

        it("should debounce multiple clicks within 500ms", () => {
            heartButton.click();
            heartButton.click();
            heartButton.click();

            jest.advanceTimersByTime(500);

            expect(mockHttpRequest.query).toHaveBeenCalledTimes(1);
        });

        it("should send only the final state after rapid clicks (1)", () => {
            heartButton.click();
            heartButton.click();
            heartButton.click();
            heartButton.click();
            heartButton.click();

            jest.advanceTimersByTime(500);

            expect(mockHttpRequest.query).toHaveBeenCalledWith(
                "/photos/test-uuid-123/fave",
                expect.objectContaining({method: "POST"})
            );
        });

        it("should send only the final state after rapid clicks (2)", () => {
            heartButton.click();
            heartButton.click();
            heartButton.click();
            heartButton.click();

            jest.advanceTimersByTime(500);

            expect(mockHttpRequest.query).toHaveBeenCalledWith(
                "/photos/test-uuid-123/fave",
                expect.objectContaining({method: "DELETE"})
            );
        });
    });

    describe("HTTP request", () => {
        let heartButton: HTMLElement;

        beforeEach(() => {
            heartButton = document.createElement("i");
            heartButton.className = "photo-fave";
            heartButton.dataset.uuid = "test-uuid-456";
            photoContainer.appendChild(heartButton);
        });

        it("should send POST request with correct parameters when favouriting", () => {
            heartButton.click();
            jest.advanceTimersByTime(500);

            expect(mockHttpRequest.query).toHaveBeenCalledWith(
                "/photos/test-uuid-456/fave",
                {
                    method: "POST",
                    headers: {"Content-Type":"application/json"},
                    signal: expect.any(AbortSignal),
                }
            );
        });

        it("should send DELETE request with 'faved' false when unfavouriting", () => {
            heartButton.classList.add("faved");
            heartButton.click();
            jest.advanceTimersByTime(500);

            expect(mockHttpRequest.query).toHaveBeenCalledWith(
                "/photos/test-uuid-456/fave",
                {
                    method: "DELETE",
                    headers: {"Content-Type":"application/json"},
                    signal: expect.any(AbortSignal),
                }
            );
        });

        it("should show success notification on successful request", async () => {
            heartButton.click();
            jest.advanceTimersByTime(500);

            await Promise.resolve();

            expect(mockNotify.success).toHaveBeenCalledWith("Success!");
        });

        it("should revert state if request fails", async () => {
            mockHttpRequest.query.mockRejectedValueOnce(new Error("Network error"));

            heartButton.click();
            expect(heartButton.classList.contains("faved")).toBe(true);

            jest.advanceTimersByTime(500);
            await Promise.resolve();
            await Promise.resolve();

            expect(heartButton.classList.contains("faved")).toBe(false);
        });

        it("should revert state if the response does not contain message", async () => {
            mockHttpRequest.query.mockResolvedValueOnce({});

            heartButton.click();
            expect(heartButton.classList.contains("faved")).toBe(true);

            jest.advanceTimersByTime(500);
            await Promise.resolve();

            expect(heartButton.classList.contains("faved")).toBe(false);
        });
    });

    describe("request abortion", () => {
        let heartButton: HTMLElement;

        beforeEach(() => {
            heartButton = document.createElement("i");
            heartButton.className = "photo-fave";
            heartButton.dataset.uuid = "test-uuid-789";
            photoContainer.appendChild(heartButton);
        });

        it("should abort pending request if the button is clicked again", () => {
            const abortSpy = jest.fn();
            global.AbortController = jest.fn().mockImplementation(() => ({
                abort: abortSpy,
                signal: {},
            })) as any;

            heartButton.click();
            jest.advanceTimersByTime(250);
            heartButton.click();

            expect(abortSpy).toHaveBeenCalled();
        });

        it("should not revert state on 'AbortError'", async () => {
            const abortError = new Error("Abort");
            abortError.name = "AbortError";
            mockHttpRequest.query.mockRejectedValueOnce(abortError);

            heartButton.click();
            jest.advanceTimersByTime(500);
            await Promise.resolve();
            await Promise.resolve();

            expect(heartButton.classList.contains("faved")).toBe(true);
        });
    });

    describe("Edge cases", () => {
        it("should handle null photoContainer gracefully", () => {
            expect(() => {
                new PhotoFave(
                    null,
                    mockHttpRequest,
                    mockRequestHeaders,
                    mockNotify,
                );
            }).not.toThrow();
        });
    });
});
