/**
 * Adapted from https://www.codingnepalweb.com/toast-notification-html-css-javascript/
 */
export class Notify
{
    constructor(type, text)
    {
        this.notifications = document.querySelector(".notifications");
        this.timer = 5000;
        this.createToast(type, text);
    }

    createToast(type, text)
    {
        const icon = this.selectIcon(type);
        const toast = document.createElement("li");
        toast.className = `toast ${type} show`;
        toast.innerHTML = `<div class="column">
                             ${icon}
                             <span>${text}</span> 
                          </div>
                          <i class=" bi bi-x" onclick="this.removeToast(this.parentElement)"></i>`;
        this.notifications.appendChild(toast);
        toast.timeoutId = setTimeout(() => this.removeToast(toast), this.timer);
    }

    removeToast(toast)
    {
        toast.classList.add("hide");
        if (toast.timeoutId) clearTimeout(toast.timeoutId);
        setTimeout(() => toast.remove(), 500);
    }

    selectIcon(icon)
    {
        switch (icon) {
            case "success":
                return "<i class='bi bi-check-circle-fill'></i>";
            case "error":
                return "<i class='bi bi-x-circle-fill'></i>";
            case "warning":
                return "<i class='bi bi-exclamation-circle-fill'></i>";
            case "info":
            default:
                return "<i class='bi bi-info-circle-fill'></i>";
        }
    }
}
