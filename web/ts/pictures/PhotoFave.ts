import {PhotoFavePersistence} from "./PhotoFavePersistence";

export class PhotoFave
{
    private heartButton: HTMLElement | null = null;
    private pendingRequest: AbortController | null = null;
    private debounceTimeout: number | null = null;

    constructor(
        private photoContainer: HTMLDivElement | null,
        private persistence: PhotoFavePersistence
    ) {
        this.events();
    }

    private events(): void
    {
        this.photoContainer?.addEventListener("photoLoaded", this.handlePhotoLoaded.bind(this));
        this.photoContainer?.addEventListener("click", this.photoFaveClickHandler.bind(this));
    }

    private handlePhotoLoaded(event: Event): void
    {
        this.heartButton = this.getHearButtonFromLoadedPhoto(event);
        if (this.heartButton === null) {
            return;
        }

        if (! this.persistence.isLoggedIn()) {
            this.toggleFaved(this.persistence.isFaved(this.heartButton.dataset.uuid || ""));
        }
    }

    private photoFaveClickHandler(event: Event): void
    {
        this.heartButton = this.getHeartButtonFromClick(event);
        if (this.heartButton === null) {
            return;
        }

        const newFaveState = !this.isFaved();
        this.toggleFaved(newFaveState);
        const photoUuid: string = this.heartButton.dataset.uuid || "";

        window.clearTimeout(this.debounceTimeout ?? 0);
        if (this.pendingRequest) {
            this.pendingRequest.abort();
        }

        const controller = new AbortController();
        this.pendingRequest = controller;

        this.debounceTimeout = window.setTimeout(() => {
            this.persistence.save(photoUuid, newFaveState, controller.signal)
                .catch((error: unknown) => {
                    if (error instanceof Error && error.name === "AbortError") {
                        return;
                    }
                    this.toggleFaved(!newFaveState);
                })
                .finally(() => {
                    if (this.pendingRequest === controller) {
                        this.pendingRequest = null;
                    }
                });
        }, 500);
    }

    private getHeartButtonFromClick(event: Event): HTMLElement | null
    {
        const heart = (event.target as HTMLElement)?.closest(".photo-fave");
        if (!heart) {
            return null;
        }
        return heart as HTMLElement;
    }

    private getHearButtonFromLoadedPhoto(event: Event): HTMLElement | null
    {
        const heart = (event.target as HTMLElement)?.querySelector(".photo-fave");
        if (!heart) {
            return null;
        }
        return heart as HTMLElement;
    }

    private isFaved(): boolean
    {
        return this.heartButton!.classList.contains("faved");
    }

    private toggleFaved(faved: boolean): void
    {
        this.heartButton!.classList.toggle("faved", faved);
    }
}
