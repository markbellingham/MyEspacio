import {NotifyInterface} from "../framework/Notification";
import {HttpRequestInterface, RequestHeadersInterface} from "../types";

export class PhotoFave
{
    private heartButton: HTMLElement | null = null;
    private pendingRequest: AbortController | null = null;
    private debounceTimeout: number | null = null;

    constructor(
        private photoContainer: HTMLDivElement | null,
        private httpRequest: HttpRequestInterface,
        private requestHeaders: RequestHeadersInterface,
        private notify: NotifyInterface,
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
        this.heartButton = this.getHeartButtonFromDom(event);
    }

    private photoFaveClickHandler(event: Event): void
    {
        this.heartButton = this.getHeartButtonFromDom(event);
        if (this.heartButton === null) {
            return;
        }

        this.toggleFaved();
        const newFaveState = this.isFaved();
        const photoUuid: string = this.heartButton.dataset.uuid || "";

        window.clearTimeout(this.debounceTimeout ?? 0);
        if (this.pendingRequest) {
            this.pendingRequest.abort();
        }

        const controller = new AbortController();
        this.pendingRequest = controller;

        this.debounceTimeout = window.setTimeout(() => {
            this.sendFaveRequest(photoUuid, newFaveState, controller);
        }, 500);
    }

    private sendFaveRequest(photoUuid: string, faved: boolean, controller: AbortController): void
    {
        this.httpRequest.query(`/photos/${photoUuid}/fave`, {
            method: faved ? "POST" : "DELETE",
            headers: this.requestHeaders.jsonWithToken(),
            signal: controller.signal,
        })
            .then((response: unknown): void => {
                if (response && typeof response === "object" && ("message" in response)) {
                    this.notify.success(response.message as string);
                    return;
                }
                this.toggleFaved();
            })
            .catch(error => {
                if (error instanceof Error && error.name === "AbortError") {
                    return;
                }
                this.toggleFaved();
            })
            .finally(() => {
                this.pendingRequest = null;
            });
    }

    private getHeartButtonFromDom(event: Event): HTMLElement | null
    {
        const heart = (event.target as HTMLElement)?.closest(".photo-fave");
        if (!heart) {
            return null;
        }
        return heart as HTMLElement;
    }

    private isFaved(): boolean
    {
        if (this.heartButton === null) {
            return false;
        }
        return this.heartButton.classList.contains("faved");
    }

    private toggleFaved(): void
    {
        if (this.isFaved()) {
            this.setAsUnfaved();
        } else {
            this.setAsFaved();
        }
    }

    private setAsFaved(): void
    {
        if (this.heartButton === null) {
            return;
        }
        this.heartButton.classList.add("faved");
    }

    private setAsUnfaved(): void
    {
        if (this.heartButton === null) {
            return;
        }
        this.heartButton.classList.remove("faved");
    }
}
