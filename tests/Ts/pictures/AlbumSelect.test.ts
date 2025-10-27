/**
 * @jest-environment jsdom
 */

import {PhotoViewer} from "../../../web/ts/pictures/PhotoViewer";
import {HttpRequestInterface} from "../../../web/ts/types";
import {Notification} from "../../../web/ts/framework/Notification";
import {UrlStateManager} from "../../../web/ts/framework/UrlStateManager";
import {AlbumSelect} from "../../../web/ts/pictures/AlbumSelect";

describe("AlbumSelect", () => {
    let albumSelectElement: HTMLSelectElement;
    let mockPhotoViewer: jest.Mocked<PhotoViewer>;
    let mockHttp: jest.Mocked<HttpRequestInterface>;
    let mockNotify: jest.Mocked<Notification>;
    let mockUrlStateManager: jest.Mocked<UrlStateManager>;

    beforeEach(() => {
        albumSelectElement = document.createElement("select");
        const option1 = document.createElement("option");
        option1.value = "album1";
        option1.textContent = "Album 1";
        const option2 = document.createElement("option");
        option2.value = "album2";
        option2.textContent = "Album 2";
        albumSelectElement.append(option1, option2);
        document.body.appendChild(albumSelectElement);

        mockPhotoViewer = {
            updatePhotoGrid: jest.fn(),
        } as unknown as jest.Mocked<PhotoViewer>;
        mockHttp = {
            query: jest.fn(),
        } as unknown as jest.Mocked<HttpRequestInterface>;
        mockUrlStateManager = {
            updatePath: jest.fn(),
        } as unknown as jest.Mocked<UrlStateManager>;
        mockNotify = {
            error: jest.fn(),
        } as unknown as jest.Mocked<Notification>;

        new AlbumSelect(
            albumSelectElement,
            mockPhotoViewer,
            mockHttp,
            mockUrlStateManager,
            mockNotify,
        );
    });

    it("updates photo grid when album is selected", async () => {
        const returnedHtml = "<div>photo grid</div>";
        const url = "/photos/album1";
        mockHttp.query.mockResolvedValue(returnedHtml);

        const option = albumSelectElement.options[0];
        option.selected = true;
        albumSelectElement.dispatchEvent(new Event("change"));

        await Promise.resolve();

        expect(mockHttp.query).toHaveBeenCalledWith(url, expect.any(Object));
        expect(mockPhotoViewer.updatePhotoGrid).toHaveBeenCalledWith(returnedHtml);
        expect(mockUrlStateManager.updatePath).toHaveBeenCalledWith(url);
    });

    it("Shows error if data is not a string", async () => {
        const returnedValue = {unexpected: "object"};
        const url = "/photos/album1";
        mockHttp.query.mockResolvedValue(returnedValue);

        const option = albumSelectElement.options[0];
        option.selected = true;
        albumSelectElement.dispatchEvent(new Event("change"));

        await Promise.resolve();

        expect(mockHttp.query).toHaveBeenCalledWith(url, expect.any(Object));
        expect(mockNotify.error).toHaveBeenCalledWith("Error loading album.");

        expect(mockPhotoViewer.updatePhotoGrid).not.toHaveBeenCalled();
        expect(mockUrlStateManager.updatePath).not.toHaveBeenCalled();
    });
});
