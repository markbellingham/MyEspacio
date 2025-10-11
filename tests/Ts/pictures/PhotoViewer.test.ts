/**
 * @jest-environment jsdom
 */

import {PhotoViewer} from "../../../web/ts/pictures/PhotoViewer";
import {HttpRequestInterface} from "../../../web/ts/types";
import {Notification} from "../../../web/ts/framework/Notification";
import {UrlStateManager} from "../../../web/ts/framework/UrlStateManager";

describe("PhotoViewer", () => {
    let photoGrid: HTMLDivElement;
    let photoView: HTMLDivElement;
    let closeButton: HTMLButtonElement;
    let mockHttp: jest.Mocked<HttpRequestInterface>;
    let mockNotify: jest.Mocked<Notification>;
    let contentElement: HTMLDivElement;
    let mockUrlStateManager: jest.Mocked<UrlStateManager>;

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
        };

        mockNotify = {
            error: jest.fn()
        } as any;
        mockUrlStateManager = {
            updatePath: jest.fn(),
            getCurrentPath: jest.fn().mockReturnValue("/photos"),
            back: jest.fn(),
            updateParam: jest.fn(),
            updateParams: jest.fn(),
            getParam: jest.fn(),
            getParams: jest.fn(),
            getPathSegments: jest.fn(),
            updateState: jest.fn(),
            getState: jest.fn(),
            replaceState: jest.fn(),
            navigateTo: jest.fn(),
            onStateChange: jest.fn(),
            clearParams: jest.fn(),
            hasParam: jest.fn(),
            getFullURL: jest.fn(),
            parseURL: jest.fn(),
            destroy: jest.fn()
        } as any;

        new PhotoViewer(photoGrid, photoView, closeButton, mockHttp, mockNotify, mockUrlStateManager);
    });

    it("updates view with photo content on image click", async () => {
        mockHttp.query.mockResolvedValue("<div>photo details</div>");

        const img = photoGrid.querySelector("img")!;
        img.click();

        await new Promise(process.nextTick); // wait for async .then

        expect(contentElement.innerHTML).toContain("photo details");
        expect(photoView.classList.contains("active")).toBe(true);
        expect(mockUrlStateManager.updatePath).toHaveBeenCalledWith("/photo/test-uuid");
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
        expect(mockUrlStateManager.updatePath).toHaveBeenCalledWith("/photo/test-uuid");
    });

    it("notifies error on invalid response", async () => {
        mockHttp.query.mockResolvedValue({unexpected: "object"} as any);

        const img = photoGrid.querySelector("img")!;
        img.click();

        await new Promise(process.nextTick);

        expect(mockNotify.error).toHaveBeenCalledWith("Error loading photo.");
        expect(mockUrlStateManager.updatePath).not.toHaveBeenCalled();
    });

    it("closes photo view on close button click", () => {
        photoView.classList.add("active");

        closeButton.click();

        expect(photoView.classList.contains("active")).toBe(false);
        expect(mockUrlStateManager.back).toHaveBeenCalledWith("/photos");
    });

    it("closes photo view on Escape key when active", () => {
        photoView.classList.add("active");

        const escapeEvent = new KeyboardEvent("keydown", { key: "Escape" });
        document.dispatchEvent(escapeEvent);

        expect(photoView.classList.contains("active")).toBe(false);
        expect(mockUrlStateManager.back).toHaveBeenCalledWith("/photos");
    });
});
