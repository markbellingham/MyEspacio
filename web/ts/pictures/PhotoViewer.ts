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

    private events()
    {
        this.photoGrid.addEventListener("click", this.handlePhotoClick.bind(this));
        this.closeButton.addEventListener("click", this.handleClose.bind(this));
        document.addEventListener("keydown", this.handleKeyDown.bind(this));
    }

    private handlePhotoClick(event: MouseEvent)
    {
        const image = (event.target as HTMLElement).closest(".grid-item img") as HTMLImageElement | null;
        if (!image?.dataset.uuid) {
            return;
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
                this.updateView(data);
                this.urlStateManager.updatePath(url);
            });
    }

    private updateView(data: string)
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
        this.closeButton.scrollIntoView({behavior: "smooth"});
    }

    private handleClose()
    {
        this.photoView.classList.remove("active");
        document.body.style.overflow = "";
        this.urlStateManager.back("/photos");
    }

    private handleKeyDown(event: KeyboardEvent)
    {
        if (event.key === "Escape" && this.photoView.classList.contains("active")) {
            this.handleClose();
        }
    }
}
