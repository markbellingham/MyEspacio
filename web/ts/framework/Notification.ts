export interface NotifyInterface {
    success(_message: string, _timeout?: number): void;
    error(_message: string, _timeout?: number): void;
    warning(_message: string, _timeout?: number): void;
    info(_message: string, _timeout?: number): void;
}

export class Notification implements NotifyInterface {

    constructor(
        private readonly notificationContainer: HTMLElement,
        private readonly timer: number = 5000
    ) {
        this.notificationContainer = notificationContainer;
        this.timer = timer;
    }

    public success(message: string, timeout?: number): void {
        this.createToast("success", message, "bi-check-circle-fill", timeout);
    }

    public error(message: string, timeout?: number): void {
        this.createToast("error", message, "bi-x-circle-fill", timeout);
    }

    public warning(message: string, timeout?: number): void {
        this.createToast("warning", message, "bi-exclamation-circle-fill", timeout);
    }

    public info(message: string, timeout?: number): void {
        this.createToast("info", message, "bi-info-circle-fill", timeout);
    }

    private createToast(
        type: string,
        text: string,
        iconClass: string,
        timeout: number = this.timer
    ): void {
        if (!this.notificationContainer) {
            throw new Error("Notification container not found.");
        }

        const toast = document.createElement("li");
        toast.className = `toast ${type} show`;

        const column = document.createElement("div");
        column.className = "column";

        const icon = document.createElement("i");
        icon.className = `bi ${iconClass}`;

        const message = document.createElement("span");
        message.textContent = text;

        const close = document.createElement("i");
        close.className = "bi bi-x";
        close.addEventListener("click", () => this.removeToast(toast));

        column.appendChild(icon);
        column.appendChild(message);

        toast.appendChild(column);
        toast.appendChild(close);

        this.notificationContainer.appendChild(toast);

        const timeoutId = window.setTimeout(() => this.removeToast(toast), timeout);
        toast.dataset.timeoutId = timeoutId.toString();
    }

    private removeToast(toast: HTMLElement): void {
        toast.classList.add("hide");

        const timeoutId = toast.dataset.timeoutId;
        if (timeoutId) {
            clearTimeout(Number(timeoutId));
        }

        setTimeout(() => toast.remove(), 500);
    }
}

export const notify = new Notification(
    document.querySelector(".notifications") as HTMLElement,
    5000
);
