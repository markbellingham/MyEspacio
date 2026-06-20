/**
 * @jest-environment jsdom
 */

import {Notification} from "../../../web/ts/framework/Notification";
import {UrlStateManager} from "../../../web/ts/framework/UrlStateManager";
import {PhotoViewer} from "../../../web/ts/pictures/PhotoViewer";
import {HttpRequestInterface} from "../../../web/ts/types";

describe("PhotoViewer", () => {
    let photoGrid: HTMLDivElement;
    let photoView: HTMLDivElement;
    let closeButton: HTMLButtonElement;
    let mockHttp: jest.Mocked<HttpRequestInterface>;
    let mockNotify: jest.Mocked<Notification>;
    let contentElement: HTMLDivElement;
    let mockUrlStateManager: jest.Mocked<UrlStateManager>;
    let prevButton: HTMLButtonElement;
    let nextButton: HTMLButtonElement;

    beforeEach(() => {
        document.body.innerHTML = "";

        photoGrid = document.createElement("div");
        photoGrid.id = "photo-grid";

        const gridItem = document.createElement("div");
        gridItem.classList.add("grid-item");
        const img = document.createElement("img");
        img.dataset.uuid = "test-uuid";
        gridItem.appendChild(img);
        photoGrid.appendChild(gridItem);

        photoView = document.createElement("div");
        photoView.id = "photo-view";
        contentElement = document.createElement("div");
        contentElement.classList.add("photo-view-content");
        photoView.appendChild(contentElement);

        const parentDiv = document.createElement("div");
        parentDiv.appendChild(photoView);
        document.body.appendChild(parentDiv);

        closeButton = document.createElement("button");
        closeButton.scrollIntoView = jest.fn();
        closeButton.classList.add("close-btn");
        document.body.appendChild(closeButton);
        document.body.appendChild(photoGrid);

        mockHttp = {
            query: jest.fn()
        } as unknown as jest.Mocked<HttpRequestInterface>;
        mockNotify = {
            error: jest.fn()
        } as unknown as jest.Mocked<Notification>;
        mockUrlStateManager = {
            getCurrentPath: jest.fn().mockReturnValue("/photos"),
            back: jest.fn(),
            updateParam: jest.fn(),
            updateParams: jest.fn(),
            getParam: jest.fn(),
            getParams: jest.fn(),
            getState: jest.fn(),
            replaceState: jest.fn(),
            navigateTo: jest.fn(),
            onStateChange: jest.fn(),
            clearParams: jest.fn(),
            hasParam: jest.fn(),
            getFullURL: jest.fn(),
            parseURL: jest.fn(),
            destroy: jest.fn(),
            getPathSegments: jest.fn().mockReturnValue(["photos", "test-album"]),
            updatePath: jest.fn(),
        } as unknown as jest.Mocked<UrlStateManager>;

        new PhotoViewer(photoGrid, photoView, closeButton, mockHttp, mockNotify, mockUrlStateManager);
    });

    it("updates view with photo content on image click", async () => {
        mockHttp.query.mockResolvedValue("<div>photo details</div>");

        const img = photoGrid.querySelector("img")!;
        img.click();

        await new Promise(process.nextTick); // wait for async .then

        expect(contentElement.innerHTML).toContain("photo details");
        expect(photoView.classList.contains("active")).toBe(true);
        expect(closeButton.classList.contains("visible")).toBe(true);
        expect(mockUrlStateManager.updatePath).toHaveBeenCalledWith("photos/test-album/photo/test-uuid");
    });

    it("replaces full HTML if response starts with <!doctype", async () => {
        const mockHtmlElement = document.createElement("html");
        const innerHTMLSetter = jest.spyOn(mockHtmlElement, "innerHTML", "set");

        Object.defineProperty(document, "documentElement", {
            configurable: true,
            get: () => mockHtmlElement,
        });

        mockHttp.query.mockResolvedValue("<!DOCTYPE html><html lang='en_GB'><body>replaced</body></html>");

        const img = photoGrid.querySelector("img")!;
        img.click();

        await new Promise(process.nextTick);

        expect(innerHTMLSetter).toHaveBeenCalledWith("<!DOCTYPE html><html lang='en_GB'><body>replaced</body></html>");
        expect(closeButton.classList.contains("visible")).toBe(true);
        expect(mockUrlStateManager.updatePath).toHaveBeenCalledWith("photos/test-album/photo/test-uuid");
    });

    it("notifies error on invalid response", async () => {
        mockHttp.query.mockResolvedValue({
            unexpected: "object"
        } as unknown as string);

        const img = photoGrid.querySelector("img")!;
        img.click();

        await new Promise(process.nextTick);

        expect(mockNotify.error).toHaveBeenCalledWith("Error loading photo.");
        expect(mockUrlStateManager.updatePath).not.toHaveBeenCalled();
    });

    it("closes photo view on close button click", () => {
        photoView.classList.add("active");
        closeButton.classList.add("visible");

        closeButton.click();

        expect(photoView.classList.contains("active")).toBe(false);
        expect(closeButton.classList.contains("visible")).toBe(false);
        expect(mockUrlStateManager.back).toHaveBeenCalledWith("/photos");
    });

    it("closes photo view on Escape key when active", () => {
        photoView.classList.add("active");
        closeButton.classList.add("visible");

        const escapeEvent = new KeyboardEvent("keydown", { key: "Escape" });
        document.dispatchEvent(escapeEvent);

        expect(photoView.classList.contains("active")).toBe(false);
        expect(closeButton.classList.contains("visible")).toBe(false);
        expect(mockUrlStateManager.back).toHaveBeenCalledWith("/photos");
    });

    it("does not process click on element without valid image or uuid", () => {
        // Create a div without an image
        const invalidDiv = document.createElement("div");
        invalidDiv.classList.add("grid-item");
        photoGrid.appendChild(invalidDiv);

        invalidDiv.click();

        expect(mockHttp.query).not.toHaveBeenCalled();
        expect(mockUrlStateManager.updatePath).not.toHaveBeenCalled();
    });

    it("does not perform close actions when photo view is not open", () => {
        // Ensure photo view is not active
        photoView.classList.remove("active");

        const photoViewer = new PhotoViewer(photoGrid, photoView, closeButton, mockHttp, mockNotify, mockUrlStateManager);
        photoViewer.closeSinglePhoto();

        expect(mockUrlStateManager.back).not.toHaveBeenCalled();
        expect(closeButton.classList.contains("visible")).toBe(false);
    });

    describe("updatePhotoGrid", () => {
        let photoViewer: PhotoViewer;

        beforeEach(() => {
            photoViewer = new PhotoViewer(photoGrid, photoView, closeButton, mockHttp, mockNotify, mockUrlStateManager);
        });

        it("closes open photo before updating grid", () => {
            photoView.classList.add("active");
            closeButton.classList.add("visible");

            photoViewer.updatePhotoGrid("<div>new grid content</div>");

            expect(photoView.classList.contains("active")).toBe(false);
            expect(closeButton.classList.contains("visible")).toBe(false);
            expect(photoGrid.innerHTML).toBe("<div>new grid content</div>");
            expect(mockUrlStateManager.back).toHaveBeenCalledWith("/photos");
        });

        it("updates grid without closing when no photo is open", () => {
            photoViewer.updatePhotoGrid("<div>new grid content</div>");

            expect(photoGrid.innerHTML).toBe("<div>new grid content</div>");
            expect(mockUrlStateManager.back).not.toHaveBeenCalled();
        });

        it("replaces full HTML if response starts with <!doctype", () => {
            const mockHtmlElement = document.createElement("html");
            const innerHTMLSetter = jest.spyOn(mockHtmlElement, "innerHTML", "set");

            Object.defineProperty(document, "documentElement", {
                configurable: true,
                get: () => mockHtmlElement,
            });

            photoViewer.updatePhotoGrid("<!DOCTYPE html><html lang='en'><body>new content</body></html>");

            expect(innerHTMLSetter).toHaveBeenCalledWith("<!DOCTYPE html><html lang='en'><body>new content</body></html>");
        });

        it("syncs currentPhotoIndex when grid is active with an open photo", () => {
            const largePhoto = document.createElement("img");
            largePhoto.dataset.uuid = "uuid-2";
            largePhoto.id = "large-photo-id";
            const largePhotoDiv = document.createElement("div");
            largePhotoDiv.id = "large-photo";
            largePhotoDiv.appendChild(largePhoto);
            document.body.appendChild(largePhotoDiv);

            photoGrid.innerHTML = "";
            ["uuid-1","uuid-2","uuid-3"].forEach(uuid => {
                const item = document.createElement("div");
                item.classList.add("grid-item");
                const img = document.createElement("img");
                img.dataset.uuid = uuid;;
                item.appendChild(img);
                photoGrid.appendChild(item);
            });

            photoGrid.classList.add("active");

            new PhotoViewer(photoGrid, photoView, closeButton, mockHttp, mockNotify, mockUrlStateManager);

            mockHttp.query.mockResolvedValue("<div>new grid content</div>");
            photoView.classList.add("active");
            document.dispatchEvent(new KeyboardEvent("keydown", { key: "ArrowRight" }));

            expect(mockHttp.query).toHaveBeenCalledWith("photo/uuid-3", expect.anything());
        });
    });

    describe("Carousel navigation", () => {
        let photoViewer: PhotoViewer;

        beforeEach(() => {
            prevButton = document.createElement("button");
            prevButton.classList.add("photo-nav-prev");
            photoView.appendChild(prevButton);

            nextButton = document.createElement("button");
            nextButton.classList.add("photo-nav-next");
            photoView.appendChild(nextButton);

            photoGrid.innerHTML = "";
            ["uuid-1", "uuid-2", "uuid-3"].forEach(uuid => {
                const item = document.createElement("div");
                item.classList.add("grid-item");
                const img = document.createElement("img");
                img.dataset.uuid = uuid;
                item.appendChild(img);
                photoGrid.appendChild(item);
            });

            mockHttp.query.mockResolvedValue("<div>photo details</div>");
            photoViewer = new PhotoViewer(photoGrid, photoView, closeButton, mockHttp, mockNotify, mockUrlStateManager);
            photoView.classList.add("active");
        });

        afterEach(() => {
            photoGrid.innerHTML = "";
            jest.restoreAllMocks();
        });

        it("navigates to the next photo on ArrowRight", async () => {
            photoViewer["currentPhotoIndex"] = 0;

            document.dispatchEvent(new KeyboardEvent("keydown", { key: "ArrowRight" }));
            await new Promise(process.nextTick);

            expect(mockHttp.query).toHaveBeenCalledWith("photo/uuid-2", expect.anything());
        });

        it("wraps to first photo when ArrowRight at last photo", async () => {
            photoViewer["currentPhotoIndex"] = 2;

            document.dispatchEvent(new KeyboardEvent("keydown", { key: "ArrowRight" }));
            await new Promise(process.nextTick);

            expect(mockHttp.query).toHaveBeenCalledWith("photo/uuid-1", expect.anything());
        });

        it("navigates to previous photo on ArrowLeft", async () => {
            photoViewer["currentPhotoIndex"] = 2;

            document.dispatchEvent(new KeyboardEvent("keydown", { key: "ArrowRight" }));
            await new Promise(process.nextTick);

            expect(mockHttp.query).toHaveBeenCalledWith("photo/uuid-1", expect.anything());
        });

        it("wraps to last photo when ArrowLeft at first photo", async () => {
            photoViewer["currentPhotoIndex"] = 0;

            document.dispatchEvent(new KeyboardEvent("keydown", { key: "ArrowRight" }));
            await new Promise(process.nextTick);

            expect(mockHttp.query).toHaveBeenCalledWith("photo/uuid-2", expect.anything());
        });

        it("does not navigate on ArrowRight when photo view is closed", async () => {
            photoView.classList.remove("active");

            document.dispatchEvent(new KeyboardEvent("keydown", { key: "ArrowRight" }));

            expect(mockHttp.query).not.toHaveBeenCalled();
        });

        it("does not navigate on ArrowLeft when photo view is closed", async () => {
            photoView.classList.remove("active");

            document.dispatchEvent(new KeyboardEvent("keydown", { key: "ArrowLeft" }));

            expect(mockHttp.query).not.toHaveBeenCalled();
        });

        it("preloads image when response contains #large-photo img", async () => {
            const decodeMock = jest.fn().mockResolvedValue(undefined);
            jest.spyOn(global, "Image").mockImplementation(() => ({
                src: "",
                decode: decodeMock,
            } as unknown as HTMLImageElement));

            mockHttp.query.mockResolvedValue("<div id='large-photo'><img alt='' src='https://large.jpg' data-uuid='uuid-2'></div>");

            const img = photoGrid.querySelector("img") as HTMLImageElement;
            img.click();

            await new Promise(process.nextTick);

            expect(decodeMock).toHaveBeenCalled();
        });

        it("continues loading if image decode fails", async () => {
            const failingImage = { src: "", decode: jest.fn().mockRejectedValue(new Error("decode failed"))};
            jest.spyOn(global, "Image").mockImplementation(() => failingImage as unknown as HTMLImageElement);

            mockHttp.query.mockResolvedValue("<div>photo details</div>");

            const img = photoGrid.querySelector("img") as HTMLImageElement;
            img.click();

            await new Promise(process.nextTick);

            expect(contentElement.innerHTML).toContain("<div>photo details</div>");
        });

        it("navigates to the next photo on click", async () => {
            photoViewer["currentPhotoIndex"] = 0;

            prevButton.click();
            await new Promise(process.nextTick);

            expect(mockHttp.query).toHaveBeenCalledWith("photo/uuid-3", expect.anything());

            nextButton.click();
            await new Promise(process.nextTick);

            expect(mockHttp.query).toHaveBeenCalledWith("photo/uuid-1", expect.anything());
        });
    });

    describe("abort controller", (() => {
        let photoViewer: PhotoViewer;
        let abortSpy: jest.SpyInstance;

        beforeEach(() => {
            const item = document.createElement("div");
            item.classList.add("grid-item");
            const img = document.createElement("img");
            img.dataset.uuid = "uuid-10";
            item.appendChild(img);
            photoGrid.appendChild(item);

            photoViewer = new PhotoViewer(photoGrid, photoView, closeButton, mockHttp, mockNotify, mockUrlStateManager);
            photoView.classList.add("active");

            abortSpy = jest.spyOn(AbortController.prototype, "abort");
        });

        afterEach(() => {
            abortSpy.mockRestore();
        });

        it("aborts the previous request when a new photo is loaded", async () => {
            mockHttp.query.mockResolvedValue("<div>new grid content</div>");

            photoViewer["loadPhoto"]("uuid-1");
            photoViewer["loadPhoto"]("uuid-2");

            expect(abortSpy).toHaveBeenCalledTimes(1);
        });

        it("passes the abort signal to httpRequest", () => {
            mockHttp.query.mockResolvedValue("<div>new grid content</div>");

            photoViewer["loadPhoto"]("uuid-1");

            expect(mockHttp.query).toHaveBeenCalledWith(
                "photo/uuid-1",
                expect.objectContaining({ signal: expect.any(AbortSignal) })
            );
        });
    }));

    describe("updatePhotoUrl album context fallback", () => {
        it("falls back to 'all' when pathSecments length is less than 2", async () => {
            mockUrlStateManager.getPathSegments.mockReturnValue(["photos"]);
            mockHttp.query.mockResolvedValue("<div>photo details</div>");

            const img = photoGrid.querySelector("img") as HTMLImageElement;
            img.click();
            await new Promise(process.nextTick);

            expect(mockUrlStateManager.updatePath).toHaveBeenCalledWith("photos/all/photo/test-uuid");
        });

        it("falls back to 'all' when pathSegments[0] is not 'photos'", async () => {
            mockUrlStateManager.getPathSegments.mockReturnValue(["albums", "test-album"]);
            mockHttp.query.mockResolvedValue("<div>photo details</div>");

            const img = photoGrid.querySelector("img") as HTMLImageElement;
            img.click();
            await new Promise(process.nextTick);

            expect(mockUrlStateManager.updatePath).toHaveBeenCalledWith("photos/all/photo/test-uuid");
        });

        it("falls back to 'all' when pathSegments[1] is empty string", async () => {
            mockUrlStateManager.getPathSegments.mockReturnValue(["photos", ""]);
            mockHttp.query.mockResolvedValue("<div>photo details</div>");

            const img = photoGrid.querySelector("img") as HTMLImageElement;
            img.click();
            await new Promise(process.nextTick);

            expect(mockUrlStateManager.updatePath).toHaveBeenCalledWith("photos/all/photo/test-uuid");
        });
    });

    it("defaults currentPhotoIndex to 0 when active grid's large photo uuid is not found", () => {
        const largePhotoDiv = document.createElement("div");
        largePhotoDiv.id = "parge-photo";
        const largePhoto = document.createElement("img");
        largePhoto.dataset.uuid = "uuid-not-in-grid";
        largePhotoDiv.appendChild(largePhoto);
        document.body.appendChild(largePhotoDiv);

        photoGrid.classList.add("active");
        const viewer = new PhotoViewer(photoGrid, photoView, closeButton, mockHttp, mockNotify, mockUrlStateManager);

        expect(viewer["currentPhotoIndex"]).toBe(0);
    });
});
