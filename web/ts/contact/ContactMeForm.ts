import {HttpRequestInterface, RequestHeadersInterface} from "../types";
import {NotifyInterface} from "../framework/Notification";
import {EditorFactory, EditorInstance} from "./EditorFactory";

export class ContactMeForm {
    private editor: EditorInstance | null = null;

    constructor(
        private contactMeForm: HTMLFormElement,
        private httpRequest: HttpRequestInterface,
        private requestHeaders: RequestHeadersInterface,
        private notify: NotifyInterface,
        private editorFactory: EditorFactory
    ) {
        this.init();
        this.events();
    }

    private init(): void {
        const placeholder = this.contactMeForm.elements.namedItem("message") as HTMLTextAreaElement;
        this.editorFactory.create(placeholder)
            .then((editor: EditorInstance) => (
                this.editor = editor)
            )
            .catch(error => console.error(error));
    }

    private events(): void {
        this.contactMeForm.addEventListener("submit", this.handleFormSubmit.bind(this));
    }

    private handleFormSubmit(event: Event): void {
        event.preventDefault();
        if (this.editor) {
            this.editor.updateSourceElement();
        }
        if (! this.contactMeForm.reportValidity()) {
            return;
        }
        const formData = new FormData(this.contactMeForm);
        this.httpRequest.query("/contact/send", {
                method: "POST",
                body: formData,
                headers: this.requestHeaders.jsonWithToken(),
            }
        )
            .then((data: unknown) => {
                if (this.hasMessage(data)) {
                    this.notify.success(data.message);
                    this.contactMeForm.reset();
                    this.editor?.setData("");
                }
            });
    }

    private hasMessage(x: unknown): x is { message: string } {
        return (
            x !== null &&
            typeof x === "object" &&
            Object.prototype.hasOwnProperty.call(x, "message") &&
            typeof (x as Record<string, unknown>).message === "string"
        );
    }
}
