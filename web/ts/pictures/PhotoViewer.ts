import {Notification} from "../framework/Notification";
import requestHeaders from "../framework/RequestHeaders";
import {UrlStateManager} from "../framework/UrlStateManager";
import {HttpRequestInterface} from "../types";

export class PhotoViewer {

    private photoUuids: string[] = [];
    private currentPhotoIndex: number = 0;
    private currentRequest: AbortController | null = null;

    constructor(
        private photoGrid: HTMLDivElement,
        private photoView: HTMLDivElement,
        private closeButton: HTMLButtonElement,
        private httpRequest: HttpRequestInterface,
        private notify: Notification,
        private urlStateManager: UrlStateManager,
    ) {
        this.getPhotoUuidsFromGrid(this.photoGrid);
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
        this.loadPhoto(image.dataset.uuid);
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
        this.getPhotoUuidsFromGrid(this.photoGrid);
    }

    private loadPhoto(uuid: string): void
    {
        this.currentRequest?.abort();
        this.currentRequest = new AbortController();

        this.httpRequest.query(`photo/${uuid}`, {
            headers: requestHeaders.html(),
            signal: this.currentRequest.signal,
        })
            .then(async (data: unknown) => {
                if (typeof data !== "string") {
                    this.notify.error("Error loading photo.");
                    return;
                }
                await this.openSinglePhoto(data);
                this.alertInterestedParties();
                this.updatePhotoUrl(uuid);
            });
    }

    private async openSinglePhoto(data: string): Promise<void>
    {
        if(data.trimStart().toLowerCase().startsWith("<!doctype")) {
            const htmlElement = document.documentElement;
            htmlElement.innerHTML = data;
            this.getPhotoUuidsFromGrid(this.photoGrid);
        } else {
            const contentElement = this.photoView.querySelector(".photo-view-content");

            const template = document.createElement("template");
            template.innerHTML = data;

            const image = template.content.querySelector("#large-photo img") as HTMLImageElement | null;

            if (image?.src) {
                await this.preloadImage(image.src);
            }

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

    private async preloadImage(src: string): Promise<void>
    {
        const image = new Image();
        image.src = src;
        try {
            await image.decode();
        } catch {
            // If decode fails, still continue
        }
    }

    private alertInterestedParties(): void
    {
        this.photoView.dispatchEvent(new CustomEvent("photoLoaded"));
    }

    private updatePhotoUrl(uuid: string): void
    {
        const pathSegments = this.urlStateManager.getPathSegments();
        let albumContext = "all";
        if (pathSegments.length >= 2 && pathSegments[0] === "photos" && pathSegments[1] !== "") {
            albumContext = pathSegments[1];
        }
        this.urlStateManager.updatePath(`photos/${albumContext}/photo/${uuid}`);
    }

    public closeSinglePhoto(): void
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
        if (event.key === "ArrowLeft") {
            this.carouselLeft();
        }
        if (event.key === "ArrowRight") {
            this.carouselRight();
        }
    }

    private getPhotoUuidsFromGrid(photoGrid: HTMLDivElement): void
    {
        this.photoUuids = Array.from(photoGrid.querySelectorAll(".grid-item img"))
            .map(img => (img as HTMLImageElement).dataset.uuid)
            .filter((uuid): uuid is string => Boolean(uuid));
        if (this.photoGrid.classList.contains("active")) {
            const largePhoto = document.querySelector("#large-photo img") as HTMLImageElement;
            const photoIndex = this.photoUuids.findIndex(uuid => uuid === largePhoto?.dataset.uuid);
            this.currentPhotoIndex = photoIndex === -1 ? 0 : photoIndex;
        }
    }

    private carouselRight(): void
    {
        if (! this.photoViewIsOpen()) {
            return;
        }
        this.currentPhotoIndex++;
        if (this.currentPhotoIndex >= this.photoUuids.length) {
            this.currentPhotoIndex = 0;
        }
        this.loadPhoto(this.photoUuids[this.currentPhotoIndex]);
    }

    private carouselLeft(): void
    {
        if (! this.photoViewIsOpen()) {
            return;
        }
        this.currentPhotoIndex--;
        if (this.currentPhotoIndex < 0) {
            this.currentPhotoIndex = this.photoUuids.length - 1;
        }
        this.loadPhoto(this.photoUuids[this.currentPhotoIndex]);
    }
}
