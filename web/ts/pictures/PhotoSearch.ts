import {PhotoViewer} from "./PhotoViewer";
import {HttpRequestInterface} from "../types";
import {UrlStateManager} from "../framework/UrlStateManager";
import {Notification} from "../framework/Notification";
import requestHeaders from "../framework/RequestHeaders";

export class PhotoSearch {
    constructor(
        private searchInput: HTMLInputElement,
        private searchButton: HTMLButtonElement,
        private clearButton: HTMLButtonElement,
        private photoViewer: PhotoViewer,
        private httpRequest: HttpRequestInterface,
        private urlStateManager: UrlStateManager,
        private notify: Notification,
    ) {
        this.events();
    }

    private events(): void
    {
        this.searchButton.addEventListener("click", this.handleSearch.bind(this));
        this.clearButton.addEventListener("click", this.handleClear.bind(this));
    }

    private handleSearch(): void
    {
        const searchTerm = this.searchInput.value.trim();
        let url = this.urlStateManager.getCurrentPath();

        if (searchTerm !== "") {
            const params = new URLSearchParams({search: searchTerm});
            url += "?" + params.toString();
        }

        this.httpRequest.query(url, {
            headers: requestHeaders.html(),
        })
            .then((data: unknown) => {
                if (typeof data !== "string") {
                    this.notify.error("Error loading search results.");
                    return;
                }
                this.photoViewer.updatePhotoGrid(data);
                const params: Record<string, string> = searchTerm === "" ? {} : {search: searchTerm};
                this.urlStateManager.updateParams(params);
            });
    }

    private handleClear(): void
    {
        this.searchInput.value = "";
        this.urlStateManager.updateParams({search: null});
        this.handleSearch();
    }
}
