export class PhotoShare
{
    constructor(
        private photoContainer: HTMLDivElement | null,
    ) {
        this.events();
    }

    events(): void
    {
        this.photoContainer?.addEventListener("click", this.handlePhotoShare.bind(this));
        document.addEventListener("click", this.hidePhotoSharePopup.bind(this));
    }

    handlePhotoShare(event: Event): void
    {
        const target = event.target as HTMLElement;

        const copyButton = target.closest(".photo-share-copy-button") as HTMLButtonElement;
        if (copyButton !== null) {
            this.copyShareLink(copyButton);
            event.stopPropagation();
            return;
        }

        const share = target.closest(".photo-share-toggle");
        if (share === null) {
            return;
        }
        const popup = this.findPhotoSharePopup();
        if (! (popup instanceof HTMLElement)) {
            return;
        }
        event.stopPropagation();
        popup.classList.toggle("visible");
    }

    copyShareLink(copyButton: HTMLButtonElement): void
    {
        const input = copyButton.previousElementSibling;
        if (! (input instanceof HTMLInputElement)) {
            return;
        }
        input.setSelectionRange(0, Number.MAX_SAFE_INTEGER);
        void navigator.clipboard.writeText(input.value)
            .catch(error => console.error("Clipboard write failed", error));
    }

    hidePhotoSharePopup(event: Event): void
    {
        const popup = this.findPhotoSharePopup();
        const target = event.target as Node | null;
        if (target === null || popup === null || popup.contains(target)) {
            return;
        }
        popup.classList.remove("visible");
    }

    findPhotoSharePopup(): HTMLElement|null
    {
        const popup = this.photoContainer?.querySelector(".photo-share-popup");
        if (! popup) {
            return null;
        }
        return popup as HTMLElement;
    }
}
