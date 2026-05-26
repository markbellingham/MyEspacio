import {PhotoShare} from "../../../web/ts/pictures/PhotoShare";

describe("PhotoShare", () => {
    let container: HTMLDivElement;
    let photoShare: PhotoShare;
    let writeTextMock: jest.Mock;

    beforeEach(() => {
        container = document.createElement("div");
        document.body.appendChild(container);

        writeTextMock = jest.fn().mockResolvedValue(undefined);
        Object.defineProperty(navigator, "clipboard", {
            value: { writeText: writeTextMock },
            configurable: true
        });

        photoShare = new PhotoShare(container);
    });

    afterEach(() => {
        document.body.removeChild(container);
        jest.restoreAllMocks();
    });

    function addPopup(): HTMLDivElement {
        const popup = document.createElement("div");
        popup.classList.add("photo-share-popup");
        container.appendChild(popup);
        return popup;
    }

    function addShareToggle(): HTMLButtonElement {
        const toggle = document.createElement("button");
        toggle.className = "photo-share-toggle";
        container.appendChild(toggle);
        return toggle;
    }

    function addCopyButton(url = "https://example.com/photo/1"): {
        input: HTMLInputElement;
        button: HTMLButtonElement;
    } {
        const input = document.createElement("input");
        input.value = url;
        const button = document.createElement("button");
        button.className = "photo-share-copy-button";
        container.appendChild(input);
        container.appendChild(button);
        return {input, button };
    }

    describe("constructor", () => {
        it("does not throw when photoContainer is null", () => {
            expect(() => new PhotoShare(null)).not.toThrow();
        });
    });

    describe("handlePhotoShare", ()  => {
        it("toggles the popup visible when the share toggle is clicked", () => {
            const popup = addPopup();
            const toggle = addShareToggle();

            toggle.click();
            expect(popup.classList.contains("visible")).toBe(true);

            toggle.click();
            expect(popup.classList.contains("visible")).toBe(false);
        });

        it("stops propagation when the share toggle is clicked", () => {
            addPopup();
            const toggle = addShareToggle();
            const event = new MouseEvent("click", { bubbles: true });
            const stopPropagationSpy = jest.spyOn(event, "stopPropagation");

            toggle.dispatchEvent(event);

            expect(stopPropagationSpy).toHaveBeenCalled();
        });

        it("does nothing when click target has no relevant ancestor", () => {
            const popup = addPopup();
            popup.classList.add("visible");
            const unrelated = document.createElement("span");
            popup.appendChild(unrelated);

            unrelated.click();

            expect(popup.classList.contains("visible")).toBe(true);
        });

        it("invokes copyShareLink when a copy button is clicked", () => {
            const { button} = addCopyButton();
            const copyShareLinkSpy = jest.spyOn(photoShare as any, "copyShareLink");

            button.click();

            expect(copyShareLinkSpy).toHaveBeenCalledWith(button);
        });
    });

    describe("copyShareLink", ()  => {
        it("writes the input value to the clipboard", () => {
            const { input, button } = addCopyButton("https://example.com/photo/42");

            button.click();

            expect(writeTextMock).toHaveBeenCalledWith(input.value);
        });

        it("does nothing if the previous sibling is not an input", () => {
            const span = document.createElement("span");
            const button = document.createElement("button");
            button.className = "photo-share-copy-button";
            container.appendChild(span);
            container.appendChild(button);

            button.click();

            expect(writeTextMock).not.toHaveBeenCalled();
        });

        it("logs an error when the clipboard write fails", async () => {
            const { button } = addCopyButton();
            const error = new Error("Clipboard unavailable");
            writeTextMock.mockRejectedValue(error);
            const consoleSpy = jest.spyOn(console, "error").mockImplementation();

            button.click();
            await new Promise(resolve => setTimeout(resolve, 0));

            expect(consoleSpy).toHaveBeenCalledWith("Clipboard write failed", error);
        });
    });

    describe("hidePhotoSharePopup", () => {
        it("removes the visible cclass when clicking outside the popup", () => {
            const popup = addPopup();
            popup.classList.add("visible");
            const outside = document.createElement("div");
            document.body.appendChild(outside);

            outside.click();

            expect(popup.classList.contains("visible")).toBe(false);
            document.body.removeChild(outside);
        });

        it("does not remove the visible class when clicking inside the popup", () => {
            const popup = addPopup();
            popup.classList.add("visible");

            popup.click();

            expect(popup.classList.contains("visible")).toBe(true);
        });

        it("does nothing when no popup exists", () => {
            expect(() => document.body.click()).not.toThrow();
        });
    });

    describe("findPhotoSharePopup", () => {
        it("returns null when no popup is present in the container", () => {
            const toggle = addShareToggle();
            expect((photoShare as PhotoShare).findPhotoSharePopup()).toBeNull();

            expect(() => toggle.click()).not.toThrow();
        });

        it("returns the popup element when it is present", () => {
            const popup = addPopup();
            expect((photoShare as PhotoShare).findPhotoSharePopup()).toBe(popup);
        });
    });
});
