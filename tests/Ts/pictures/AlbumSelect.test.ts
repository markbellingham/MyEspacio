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
            getParams: jest.fn().mockReturnValue({} as Record<string, string>),
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

    afterEach(() => {
        document.body.innerHTML = "";
    });

    test.each([
        [
            "without params",
            0,
            {} as Record<string, string>,
            "/photos/album1",
            "<div>photo grid</div>"
        ],
        [
            "with params",
            1,
            {search: "search term", page: "2"} as Record<string, string>,
            "/photos/album2?search=search+term&page=2",
            "<div>different photo grid</div>"
        ],
    ])("updates photo grid when album is selected", async (
        _title: string,
        selectedOption: number,
        params: Record<string, string>,
        expectedUrl: string,
        returnedHtml: string
    ) => {
        mockHttp.query.mockResolvedValue(returnedHtml);
        mockUrlStateManager.getParams.mockReturnValue(params);

        const option = albumSelectElement.options[selectedOption];
        option.selected = true;
        albumSelectElement.dispatchEvent(new Event("change"));

        await Promise.resolve();

        expect(mockHttp.query).toHaveBeenCalledWith(expectedUrl, expect.any(Object));
        expect(mockPhotoViewer.updatePhotoGrid).toHaveBeenCalledWith(returnedHtml);
        expect(mockUrlStateManager.updatePath).toHaveBeenCalledWith(expectedUrl);
    });

    it("Shows error if data is not a string", async () => {
        const url = "/photos/album1";
        mockHttp.query.mockResolvedValue({unexpected: "object"});

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
