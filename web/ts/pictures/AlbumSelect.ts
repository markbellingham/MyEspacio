import {HttpRequestInterface} from "../types";
import {Notification} from "../framework/Notification";
import requestHeaders from "../framework/RequestHeaders";
import {UrlStateManager} from "../framework/UrlStateManager";
import {PhotoViewer} from "./PhotoViewer";

export class AlbumSelect {
    constructor(
        private albumSelector: HTMLSelectElement,
        private photoViewer: PhotoViewer,
        private httpRequest: HttpRequestInterface,
        private urlStateManager: UrlStateManager,
        private notify: Notification,
    ) {
        this.events();
    }

    private events(): void
    {
        this.albumSelector.addEventListener("change", this.handleAlbumChange.bind(this));
    }

    private handleAlbumChange(event: Event): void
    {
        const album = (event.target as HTMLSelectElement).value;
        const url = `/photos/${album}`;
        this.httpRequest.query(url, {
            headers: requestHeaders.html(),
        })
            .then((data: unknown) => {
                if (typeof data !== "string") {
                    this.notify.error("Error loading album.");
                    return;
                }
                this.photoViewer.updatePhotoGrid(data);
                this.urlStateManager.updatePath(url);
            });
    }
}
