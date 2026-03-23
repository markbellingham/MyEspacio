import {PhotoFave} from "../../../web/ts/pictures/PhotoFave";
import {PhotoFavePersistence} from "../../../web/ts/pictures/PhotoFavePersistence";

describe("PhotoFave", () => {
    let photoContainer: HTMLDivElement;
    let mockPersistence: jest.Mocked<PhotoFavePersistence>;

    beforeEach(() => {
        photoContainer = document.createElement("div");
        mockPersistence = {
            save: jest.fn().mockResolvedValue(undefined)
        } as unknown as jest.Mocked<PhotoFavePersistence>;

        jest.useFakeTimers();
    });

    afterEach(() => {
        document.body.innerHTML = "";
        jest.clearAllTimers();
        jest.clearAllMocks();
        jest.restoreAllMocks();
        jest.useRealTimers();
    });

    describe("Initialisation", () => {
        it("should initialise with event listeners added", () => {
            const addEventListenerSpy = jest.spyOn(photoContainer, "addEventListener");

            new PhotoFave(photoContainer, mockPersistence);

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

            new PhotoFave(photoContainer, mockPersistence);

            const event = new CustomEvent("photoLoaded", {
                bubbles: true,
                detail: {},
            });
            Object.defineProperty(event, "target", {value: heartButton, enumerable: true});

            photoContainer.dispatchEvent(event);
            heartButton.click();

            expect(heartButton.classList.contains("faved")).toBe(true);
        });

        it("should not throw when photoLoaded target is invalid", () => {
            const event = new CustomEvent("photoLoaded", {bubbles: true});
            Object.defineProperty(event, "target", {value: document.createElement("div")});

            expect(() => photoContainer.dispatchEvent(event)).not.toThrow();
        });
    });

    describe("click handling", () => {
        let heartButton: HTMLElement;

        beforeEach(() => {
            heartButton = document.createElement("i");
            heartButton.className = "photo-fave";
            heartButton.dataset.uuid = "test-uuid-123";
            photoContainer.appendChild(heartButton);

            new PhotoFave(photoContainer, mockPersistence);
        });

        it("should toggle the fave class when clicked", () => {
            expect(heartButton.classList.contains("faved")).toBe(false);

            heartButton.click();

            expect(heartButton.classList.contains("faved")).toBe(true);
        });

        it("should send save requests through persistence after debounce", () => {
            heartButton.click();
            jest.advanceTimersByTime(500);

            expect(mockPersistence.save).toHaveBeenCalledWith(
                "test-uuid-123",
                true,
                expect.any(AbortSignal)
            );
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
            expect(mockPersistence.save).not.toHaveBeenCalled();
        });

        it("should work with nested elements using event delegation", () => {
            const icon = document.createElement("i");
            heartButton.append(icon);

            icon.click();

            expect(heartButton.classList.contains("faved")).toBe(true);
        });

        it("should revert UI when save fails", async () => {
            mockPersistence.save.mockRejectedValue(new Error("Network error"));

            heartButton.click();
            jest.advanceTimersByTime(500);
            await Promise.resolve();

            expect(heartButton.classList.contains("faved")).toBe(false);
        });

        it("should not revert UI on AbortError", async () => {
            const abortError = new Error("Abort");
            abortError.name = "AbortError";
            mockPersistence.save.mockRejectedValue(abortError);

            heartButton.click();
            jest.advanceTimersByTime(500);
            await Promise.resolve();

            expect(heartButton.classList.contains("faved")).toBe(true);
        });

        it("should abort the previous pending request when clicked again", async () => {
            const abortSpy = jest.fn();
            const abortControllerMock = {
                abort: abortSpy,
                signal: {} as AbortSignal,
            };

            jest.spyOn(global, "AbortController").mockImplementation(() => abortControllerMock as AbortController);

            heartButton.click();
            jest.advanceTimersByTime(500);
            heartButton.click();

            expect(abortSpy).toHaveBeenCalled();
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

            new PhotoFave(photoContainer, mockPersistence);
        });

        it("should debounce multiple clicks within 500ms", () => {
            heartButton.click();
            heartButton.click();
            heartButton.click();

            jest.advanceTimersByTime(500);

            expect(mockPersistence.save).toHaveBeenCalledTimes(1);
        });

        it("should send only the final state after rapid clicks (1)", () => {
            heartButton.click();
            heartButton.click();
            heartButton.click();
            heartButton.click();
            heartButton.click();

            jest.advanceTimersByTime(500);

            expect(mockPersistence.save).toHaveBeenCalledTimes(1);
            expect(mockPersistence.save).toHaveBeenCalledWith(
                "test-uuid-123",
                true,
                expect.any(AbortSignal)
            );
        });

        it("should send only the final state after rapid clicks (2)", () => {
            heartButton.click();
            heartButton.click();
            heartButton.click();
            heartButton.click();

            jest.advanceTimersByTime(500);

            expect(mockPersistence.save).toHaveBeenCalledTimes(1);
            expect(mockPersistence.save).toHaveBeenCalledWith(
                "test-uuid-123",
                false,
                expect.any(AbortSignal)
            );
        });
    });

    describe("request abortion", () => {
        let heartButton: HTMLElement;

        beforeEach(() => {
            heartButton = document.createElement("i");
            heartButton.className = "photo-fave";
            heartButton.dataset.uuid = "test-uuid-789";
            photoContainer.appendChild(heartButton);

            new PhotoFave(photoContainer, mockPersistence);
        });

        it("should abort pending request if the button is clicked again", () => {
            const abortSpy = jest.fn();
            const abortControllerMock = {
                abort: abortSpy,
                signal: {} as AbortSignal,
            };

            const abortController = jest.spyOn(global, "AbortController").mockImplementation(() => abortControllerMock as AbortController);

            heartButton.click();
            jest.advanceTimersByTime(250);
            heartButton.click();

            expect(abortSpy).toHaveBeenCalled();
            abortController.mockRestore();
        });

        it("should not revert state on 'AbortError'", async () => {
            heartButton.click();
            jest.advanceTimersByTime(500);
            await Promise.resolve();

            expect(heartButton.classList.contains("faved")).toBe(true);
        });
    });

    describe("Edge cases", () => {
        it("should handle null photoContainer gracefully", () => {
            expect(() => {
                new PhotoFave(null, mockPersistence);
            }).not.toThrow();
        });
    });
});
