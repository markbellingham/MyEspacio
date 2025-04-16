interface NotifyInterface {
    success(message: string): void;

    error(message: string): void;

    warning(message: string): void;

    info(message: string): void;
}

export class Notification implements NotifyInterface {
    private notifications: HTMLDivElement | null = document.querySelector(".notifications");
    private timer: number = 5000;

    public success(message: string): void {
        this.createToast("success", message, "bi-check-circle-fill");
    }

    public error(message: string): void {
        this.createToast("error", message, "bi-x-circle-fill");
    }

    public warning(message: string): void {
        this.createToast("warning", message, "bi-exclamation-circle-fill");
    }

    public info(message: string): void {
        this.createToast("info", message, "bi-info-circle-fill");
    }

    private createToast(type: string, text: string, iconClass: string): void {
        if (!this.notifications) {
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

        this.notifications.appendChild(toast);

        const timeoutId = window.setTimeout(() => this.removeToast(toast), this.timer);
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

export const notify = new Notification();
