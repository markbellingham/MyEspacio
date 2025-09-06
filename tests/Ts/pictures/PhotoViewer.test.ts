/**
 * @jest-environment jsdom
 */

import {PhotoViewer} from "../../../web/ts/pictures/PhotoViewer";
import {HttpRequestInterface} from "../../../web/ts/types";
import {Notification} from "../../../web/ts/framework/Notification";

describe("PhotoViewer", () => {
    let photoGrid: HTMLDivElement;
    let photoView: HTMLDivElement;
    let closeButton: HTMLButtonElement;
    let mockHttp: jest.Mocked<HttpRequestInterface>;
    let mockNotify: jest.Mocked<Notification>;
    let contentElement: HTMLDivElement;

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

        new PhotoViewer(photoGrid, photoView, closeButton, mockHttp, mockNotify);
    });

    it("updates view with photo content on image click", async () => {
        mockHttp.query.mockResolvedValue("<div>photo details</div>");

        const img = photoGrid.querySelector("img")!;
        img.click();

        await new Promise(process.nextTick); // wait for async .then

        expect(contentElement.innerHTML).toContain("photo details");
        expect(photoView.classList.contains("active")).toBe(true);
        expect(photoGrid.classList.contains("single-column")).toBe(true);
        expect(document.body.style.overflow).toBe("hidden");
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
    });

    it("notifies error on invalid response", async () => {
        mockHttp.query.mockResolvedValue({unexpected: "object"} as any);

        const img = photoGrid.querySelector("img")!;
        img.click();

        await new Promise(process.nextTick);

        expect(mockNotify.error).toHaveBeenCalledWith("Error loading photo.");
    });

    it("closes photo view on close button click", () => {
        photoView.classList.add("active");
        photoGrid.classList.add("single-column");
        document.body.style.overflow = "hidden";

        closeButton.click();

        expect(photoView.classList.contains("active")).toBe(false);
        expect(photoGrid.classList.contains("single-column")).toBe(false);
        expect(document.body.style.overflow).toBe("");
    });

    it("closes photo view on Escape key when active", () => {
        photoView.classList.add("active");
        photoGrid.classList.add("single-column");
        document.body.style.overflow = "hidden";

        const escapeEvent = new KeyboardEvent("keydown", { key: "Escape" });
        document.dispatchEvent(escapeEvent);

        expect(photoView.classList.contains("active")).toBe(false);
        expect(photoGrid.classList.contains("single-column")).toBe(false);
        expect(document.body.style.overflow).toBe("");
    });
});
