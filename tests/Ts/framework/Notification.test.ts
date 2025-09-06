import {Notification} from "../../../web/ts/framework/Notification";

describe("Notification", () => {
    let container: HTMLElement;
    let notifier: Notification;

    beforeEach(() => {
        // Set up a clean DOM environment for each test
        document.body.innerHTML = "";
        container = document.createElement("ul");
        container.className = "notifications";
        document.body.appendChild(container);
        notifier = new Notification(container, 2000);
    });

    afterEach(() => {
        // Clean up after each test
        document.body.innerHTML = "";
        jest.useRealTimers();
    });

    // Test each notification type using parameterized tests
    describe.each([
        ["success", "bi-check-circle-fill", "success"],
        ["error", "bi-x-circle-fill", "error"],
        ["info", "bi-info-circle-fill", "info"],
        ["warning", "bi-exclamation-circle-fill", "warning"]
    ])("%s notification", (method, iconClass, toastClass) => {
        test(`adds a ${method} toast with correct styling and structure`, () => {
            (notifier as any)[method]("message");

            const toast = container.querySelector(`.toast.${toastClass}`) as HTMLElement;
            expect(toast).not.toBeNull();

            // Check for show class
            expect(toast.classList.contains("show")).toBe(true);

            // Verify structure
            const column = toast.querySelector(".column");
            expect(column).not.toBeNull();

            const icon = column?.querySelector(`.bi.${iconClass}`);
            expect(icon).not.toBeNull();

            const messageSpan = column?.querySelector("span");
            expect(messageSpan?.textContent).toBe("message");

            const closeButton = toast.querySelector(".bi.bi-x");
            expect(closeButton).not.toBeNull();
        });
    });

    test("removes toast after default timeout", () => {
        jest.useFakeTimers();
        notifier.info("This will disappear after the default timeout");

        const toast = container.querySelector(".toast.info") as HTMLElement;
        expect(toast).toBeTruthy();

        // Should have a timeout ID stored in dataset
        expect(toast.dataset.timeoutId).toBeDefined();

        // Should still be in DOM before default timeout (2000ms)
        jest.advanceTimersByTime(1999);
        expect(container.contains(toast)).toBe(true);

        // Should be removed after default timeout + animation delay
        jest.advanceTimersByTime(1);
        expect(toast.classList.contains("hide")).toBe(true);

        jest.advanceTimersByTime(500); // Animation delay
        expect(container.contains(toast)).toBe(false);
    });

    test("respects custom timeout", () => {
        jest.useFakeTimers();
        notifier.info("This will disappear in 1s", 1000);

        const toast = container.querySelector(".toast.info") as HTMLElement;
        expect(toast).toBeTruthy();

        // Should still be in DOM before timeout
        jest.advanceTimersByTime(999);
        expect(container.contains(toast)).toBe(true);

        // Should be removed after timeout + 500ms animation delay
        jest.advanceTimersByTime(1);
        expect(toast.classList.contains("hide")).toBe(true);

        jest.advanceTimersByTime(500);
        expect(container.contains(toast)).toBe(false);
    });

    test("throws if container is missing", () => {
        const badNotifier = new Notification(null as any);
        expect(() => badNotifier.error("Oops")).toThrow("Notification container not found.");
    });

    test("can display multiple notifications", () => {
        notifier.success("Success message");
        notifier.error("Error message");

        const toasts = container.querySelectorAll(".toast");
        expect(toasts.length).toBe(2);
        expect(container.querySelector(".toast.success")?.textContent).toContain("Success message");
        expect(container.querySelector(".toast.error")?.textContent).toContain("Error message");
    });

    test("can be dismissed manually", () => {
        jest.useFakeTimers();
        notifier.info("Dismissable message");

        const toast = container.querySelector(".toast.info") as HTMLElement;
        const timeoutId = toast.dataset.timeoutId;
        expect(timeoutId).toBeDefined();

        // Simulate clicking close button
        const closeBtn = toast.querySelector(".bi.bi-x");
        closeBtn?.dispatchEvent(new MouseEvent("click"));

        // Should add hide class immediately
        expect(toast.classList.contains("hide")).toBe(true);

        // Should be removed after animation delay
        jest.advanceTimersByTime(500);
        expect(container.contains(toast)).toBe(false);

        // Test that the timeout was cleared by advancing time and making
        // sure no additional attempts to remove happen
        const origRemove = HTMLElement.prototype.remove;
        let removeCount = 0;
        HTMLElement.prototype.remove = function() {
            removeCount++;
            return origRemove.apply(this);
        };

        jest.advanceTimersByTime(5000); // Far beyond the timeout
        expect(removeCount).toBe(0); // No additional removes should happen

        // Restore original
        HTMLElement.prototype.remove = origRemove;
    });

    test("handles empty messages", () => {
        notifier.info("");
        const toast = container.querySelector(".toast.info");
        expect(toast).not.toBeNull();
        const message = toast?.querySelector("span");
        expect(message?.textContent).toBe("");
    });
});
