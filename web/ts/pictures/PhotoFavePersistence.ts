import {NotifyInterface} from "../framework/Notification";
import {HttpRequestInterface, RequestHeadersInterface} from "../types";
import AuthState from "../user/AuthState";

export interface PhotoFavePersistenceInterface
{
    isFaved(photoUuid: string): boolean;
    load(): Set<string>;
    save(photoUuid: string, faved: boolean, signal: AbortSignal): Promise<void>;
}

export class PhotoFavePersistence implements PhotoFavePersistenceInterface
{
    private readonly STORAGE_KEY: string = "favedPhotos";

    constructor(
        private httpRequest: HttpRequestInterface,
        private requestHeaders: RequestHeadersInterface,
        private notify: NotifyInterface,
        private authState: AuthState,
    ) {}

    isFaved(photoUuid: string): boolean
    {
        return this.load().has(photoUuid);
    }

    load(): Set<string>
    {
        try {
            const raw = window.localStorage.getItem(this.STORAGE_KEY);
            const array = raw ? JSON.parse(raw) : [];
            return new Set(Array.isArray(array) ? array.filter(value => typeof value === "string") : []);
        } catch {
            return new Set();
        }
    }

    save(photoUuid: string, faved: boolean, signal: AbortSignal): Promise<void>
    {
        if (faved) {
            this.add(photoUuid);
        } else {
            this.remove(photoUuid);
        }
        const faves = this.load();
        window.localStorage.setItem(this.STORAGE_KEY, JSON.stringify(Array.from(faves)));

        if (!this.authState.isLoggedIn()) {
            return Promise.resolve();
        }

        return this.saveRemote(photoUuid, faved, signal);
    }

    isLoggedIn(): boolean
    {
        return this.authState.isLoggedIn();
    }

    private saveLocal(faves: Set<string>): void
    {
        window.localStorage.setItem(this.STORAGE_KEY, JSON.stringify(Array.from(faves)));
    }

    private saveRemote(photoUuid: string, faved: boolean, signal: AbortSignal): Promise<void>
    {
        return this.httpRequest.query(`/photos/${photoUuid}/fave`, {
            method: faved ? "POST" : "DELETE",
            headers: this.requestHeaders.jsonWithToken(),
            signal: signal,
        }).then((response) => {
            if (response && typeof response === "object" && ("message" in response)) {
                this.notify.success(response.message as string);
                return;
            }
        })
            .catch((error: unknown) => {
                if (error instanceof Error && error.name === "AbortError") {
                    return;
                }
                throw error;
            });
    }

    private add(photoUuid: string): void
    {
        const faves = this.load();
        faves.add(photoUuid);
        this.saveLocal(faves);
    }

    private remove(photoUuid: string): void
    {
        const faves = this.load();
        faves.delete(photoUuid);
        this.saveLocal(faves);
    }
}
