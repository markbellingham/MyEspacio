import {PhotoViewer} from "./PhotoViewer";
import {httpRequest} from "../framework/HttpRequest";
import {notify} from "../framework/Notification";
import {urlStateManager} from "../framework/UrlStateManager";
import {AlbumSelect} from "./AlbumSelect";
import {PhotoSearch} from "./PhotoSearch";

document.addEventListener("DOMContentLoaded", function () {
    const url = new URL(window.location.href);
    if (! url.pathname.startsWith("/photos") ) {
        return;
    }

    const searchInput = document.querySelector("#photo-search-input") as HTMLInputElement;
    const searchButton = document.querySelector("#photo-search-btn") as HTMLButtonElement;
    const clearButton = document.querySelector("#clear-search-btn") as HTMLButtonElement;
    const albumSelect = document.querySelector("#photo-album-select") as HTMLSelectElement | null;
    const photoGrid = document.querySelector("#photos") as HTMLDivElement | null;
    const photoView = document.querySelector("#photo-view") as HTMLDivElement | null;
    const closeBtn = document.querySelector(".close-btn") as HTMLButtonElement | null;

    if (!photoGrid || !photoView || !closeBtn || !albumSelect || !searchInput || !searchButton) {
        console.error({photoGrid, photoView, closeBtn, albumSelect, searchInput, searchButton});
        console.error("PhotoViewer: Required elements not found in DOM.");
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
    new PhotoSearch(
        searchInput,
        searchButton,
        clearButton,
        photoViewer,
        httpRequest,
        urlStateManager,
        notify,
    );
    new AlbumSelect(
        albumSelect,
        photoViewer,
        httpRequest,
        urlStateManager,
        notify,
    );
});
