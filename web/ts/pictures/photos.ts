import {PhotoViewer} from "./PhotoViewer";
import {httpRequest} from "../framework/HttpRequest";
import {notify} from "../framework/Notification";
import {urlStateManager} from "../framework/UrlStateManager";
import {AlbumSelect} from "./AlbumSelect";

document.addEventListener("DOMContentLoaded", function () {
    const albumSelect = document.querySelector("#photo-album-select") as HTMLSelectElement | null;
    const photoGrid = document.querySelector("#photos") as HTMLDivElement | null;
    const photoView = document.querySelector("#photo-view") as HTMLDivElement | null;
    const closeBtn = document.querySelector(".close-btn") as HTMLButtonElement | null;

    if (!photoGrid || !photoView || !closeBtn || !albumSelect) {
        // console.error({photoGrid, photoView, closeBtn, albumSelect});
        // console.error("PhotoViewer: Required elements not found in DOM.");
        return;
    }

    const photoViewer = new PhotoViewer(
        photoGrid,
        photoView,
        closeBtn,
        httpRequest,
        notify,
        urlStateManager
    );
    new AlbumSelect(
        albumSelect,
        photoViewer,
        httpRequest,
        urlStateManager,
        notify,
    );
});
