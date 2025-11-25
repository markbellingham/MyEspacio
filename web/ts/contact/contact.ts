import {notify} from "../framework/Notification";
import {ContactMeForm} from "./ContactMeForm";
import {HttpRequest} from "../framework/HttpRequest";
import requestHeaders from "../framework/RequestHeaders";
import ckEditorAdapter from "./ckEditorAdapter";

document.addEventListener("DOMContentLoaded", () => {
    const url = new URL(window.location.href);
    if (! url.pathname.startsWith("/contact")) {
        return;
    }

    const contactMeForm = document.querySelector("#send-email-form") as HTMLFormElement;

    if (contactMeForm === null) {
        console.error("ContactMeForm: Required elements not found in DOM.");
    }

    new ContactMeForm(
        contactMeForm,
        new HttpRequest(notify),
        requestHeaders,
        notify,
        ckEditorAdapter
    );
});
