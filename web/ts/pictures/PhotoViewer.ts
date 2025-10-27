import {HttpRequestInterface} from "../types";
import requestHeaders from "../framework/RequestHeaders";
import {Notification} from "../framework/Notification";
import {UrlStateManager} from "../framework/UrlStateManager";

export class PhotoViewer {

    constructor(
        private photoGrid: HTMLDivElement,
        private photoView: HTMLDivElement,
        private closeButton: HTMLButtonElement,
        private httpRequest: HttpRequestInterface,
        private notify: Notification,
        private urlStateManager: UrlStateManager,
    ) {
        this.events();
    }

    private events(): void
    {
        this.photoGrid.addEventListener("click", this.handlePhotoClick.bind(this));
        this.closeButton.addEventListener("click", this.closeSinglePhoto.bind(this));
        document.addEventListener("keydown", this.handleKeyDown.bind(this));
    }

    private handlePhotoClick(event: MouseEvent): void
    {
        const image = (event.target as HTMLElement).closest(".grid-item img") as HTMLImageElement | null;
        if (!image?.dataset.uuid) {
            return;
        }
        const pathSegments = this.urlStateManager.getPathSegments();
        let albumContext = "all";
        if (pathSegments.length >= 2 && pathSegments[0] === "photos" && pathSegments[1] !== "") {
            albumContext = pathSegments[1];
        }

        const url = `/photo/${image.dataset.uuid}`;
        this.httpRequest.query(url, {
            headers: requestHeaders.html(),
        })
            .then((data: unknown) => {
                if (typeof data !== "string") {
                    this.notify.error("Error loading photo.");
                    return;
                }
                this.openSinglePhoto(data);
                this.urlStateManager.updatePath(`photos/${albumContext}` + url);
            });
    }

    public updatePhotoGrid(data: string): void
    {
        if (this.photoViewIsOpen()) {
            this.closeSinglePhoto();
        }

        if (data.trimStart().toLowerCase().startsWith("<!doctype")) {
            document.documentElement.innerHTML = data;
        } else {
            this.photoGrid.innerHTML = data;
        }
    }


    openSinglePhoto(data: string): void
    {
        if(data.trimStart().toLowerCase().startsWith("<!doctype")) {
            const htmlElement = document.documentElement;
            htmlElement.innerHTML = data;
        } else {
            const contentElement = this.photoView.querySelector(".photo-view-content");
            if (contentElement) {
                contentElement.innerHTML = data;
            }
            if (this.photoView.parentElement) {
                const parentWidth = this.photoView.parentElement.clientWidth;
                this.photoView.style.width = parentWidth + "px";
            }
        }
        this.photoView.classList.add("active");
        this.closeButton.classList.add("visible");
        this.closeButton.scrollIntoView({behavior: "smooth"});
    }

    closeSinglePhoto(): void
    {
        if (! this.photoViewIsOpen()) {
            return;
        }
        this.photoView.classList.remove("active");
        document.body.style.overflow = "";
        this.urlStateManager.back("/photos");
        this.closeButton.classList.remove("visible");
    }

    private photoViewIsOpen(): boolean
    {
        return this.photoView.classList.contains("active");
    }

    private handleKeyDown(event: KeyboardEvent): void
    {
        if (event.key === "Escape" && this.photoView.classList.contains("active")) {
            this.closeSinglePhoto();
        }
    }
}
