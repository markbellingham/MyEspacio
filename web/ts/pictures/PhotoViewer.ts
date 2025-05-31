import {HttpRequestInterface} from "../types";
import requestHeaders from "../framework/RequestHeaders";
import {Notification} from "../framework/Notification";

export class PhotoViewer {

    constructor(
        private photoGrid: HTMLDivElement,
        private photoView: HTMLDivElement,
        private closeButton: HTMLButtonElement,
        private httpRequest: HttpRequestInterface,
        private notify: Notification,
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
        this.httpRequest.query(`/photo/${image.dataset.uuid}`, {
            headers: requestHeaders.html(),
        })
            .then((data: unknown) => {
                if (typeof data !== "string") {
                    this.notify.error("Error loading photo.");
                    return;
                }
                this.updateView(data);
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
        this.photoGrid.classList.add("single-column");
        document.body.style.overflow = "hidden";
        this.closeButton.scrollIntoView({behavior: "smooth"});
    }

    private handleClose()
    {
        this.photoView.classList.remove("active");
        this.photoGrid.classList.remove("single-column");
        document.body.style.overflow = "";
    }

    private handleKeyDown(event: KeyboardEvent)
    {
        if (event.key === "Escape" && this.photoView.classList.contains("active")) {
            this.handleClose();
        }
    }
}
