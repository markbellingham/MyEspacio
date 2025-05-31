import {PhotoViewer} from "./PhotoViewer";
import {httpRequest} from "../framework/HttpRequest";
import {notify} from "../framework/Notification";

document.addEventListener("DOMContentLoaded", function () {
    const photoGrid = document.querySelector("#photo-grid") as HTMLDivElement | null;
    const photoView = document.querySelector("#photo-view") as HTMLDivElement | null;
    const closeBtn = document.querySelector(".close-btn") as HTMLButtonElement | null;

    if (!photoGrid || !photoView || !closeBtn) {
        console.error("PhotoViewer: Required elements not found in DOM.");
        return;
    }

    new PhotoViewer(photoGrid, photoView, closeBtn, httpRequest, notify);
});
