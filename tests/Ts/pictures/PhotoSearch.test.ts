import {PhotoViewer} from "../../../web/ts/pictures/PhotoViewer";
import {HttpRequestInterface} from "../../../web/ts/types";
import {Notification} from "../../../web/ts/framework/Notification";
import {UrlStateManager} from "../../../web/ts/framework/UrlStateManager";
import {PhotoSearch} from "../../../web/ts/pictures/PhotoSearch";

describe("PhotoSearch", () => {
    let searchInput: HTMLInputElement;
    let searchButton: HTMLButtonElement;
    let clearButton: HTMLButtonElement;
    let mockPhotoViewer: jest.Mocked<PhotoViewer>;
    let mockHttp: jest.Mocked<HttpRequestInterface>;
    let mockNotify: jest.Mocked<Notification>;
    let mockUrlStateManager: jest.Mocked<UrlStateManager>;

    beforeEach(() => {
        const fragment = document.createDocumentFragment();
        searchInput = document.createElement("input");
        searchButton = document.createElement("button");
        clearButton = document.createElement("button");
        fragment.appendChild(searchInput);
        fragment.appendChild(searchButton);
        document.body.appendChild(fragment);

        mockPhotoViewer = {
            updatePhotoGrid: jest.fn(),
        } as unknown as jest.Mocked<PhotoViewer>;
        mockHttp = {
            query: jest.fn(),
        } as unknown as jest.Mocked<HttpRequestInterface>;
        mockUrlStateManager = {
            getCurrentPath: jest.fn().mockReturnValue("/photos"),
            updateParams: jest.fn(),
            getParams: jest.fn().mockReturnValue({search: "search term"} as Record<string, string>),
        } as unknown as jest.Mocked<UrlStateManager>;
        mockNotify = {
            error: jest.fn(),
        } as unknown as jest.Mocked<Notification>;

        new PhotoSearch(
            searchInput,
            searchButton,
            clearButton,
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
            "search 1",
            "search term",
            {search: "search term"} as Record<string, string>,
            "/photos?search=search+term",
            "<div>photo grid</div>"
        ]
    ])("Updates photo grid when search is performed", async (
        _title: string,
        searchTerm: string,
        params: Record<string, string>,
        expectedUrl: string,
        returnedHtml: string,
    ) => {
        mockHttp.query.mockResolvedValue(returnedHtml);

        searchInput.value = searchTerm;
        searchButton.click();

        await Promise.resolve();

        expect(mockHttp.query).toHaveBeenCalledWith(expectedUrl, expect.any(Object));
        expect(mockPhotoViewer.updatePhotoGrid).toHaveBeenCalledWith(returnedHtml);
        expect(mockUrlStateManager.updateParams).toHaveBeenCalledWith(params);
    });

    test.each([
        [
            "search 2",
            "search term",
            "/photos?search=search+term",
            {unexpected: "object"}
        ]
    ]) ("Shows error if data is not a string", async (
        _title: string,
        searchTerm: string,
        expectedUrl: string,
        returnedValue: unknown,
    ) => {
        mockHttp.query.mockResolvedValue(returnedValue);

        searchInput.value = searchTerm;
        searchButton.click();

        await Promise.resolve();

        expect(mockHttp.query).toHaveBeenCalledWith(expectedUrl, expect.any(Object));
        expect(mockNotify.error).toHaveBeenCalledWith("Error loading search results.");

        expect(mockPhotoViewer.updatePhotoGrid).not.toHaveBeenCalled();
        expect(mockUrlStateManager.updateParams).not.toHaveBeenCalled();
    });

    it("Clears search input and params when clear button is clicked", async () => {
        mockHttp.query.mockResolvedValue("<div>photo grid</div>");
        expect(mockUrlStateManager.getParams()).toEqual({search: "search term"} as Record<string, string>);

        searchInput.value = "search term";
        clearButton.click();

        await Promise.resolve();

        expect(searchInput.value).toBe("");
        expect(mockUrlStateManager.updateParams).toHaveBeenCalledWith({});

        expect(mockHttp.query).toHaveBeenCalledWith("/photos", expect.any(Object));
        expect(mockPhotoViewer.updatePhotoGrid).toHaveBeenCalledWith("<div>photo grid</div>");
    });
});
