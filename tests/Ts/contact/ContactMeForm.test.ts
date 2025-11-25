import {HttpRequestInterface, RequestHeadersInterface} from "../../../web/ts/types";
import {NotifyInterface} from "../../../web/ts/framework/Notification";
import {ContactMeForm} from "../../../web/ts/contact/ContactMeForm";
import {EditorFactory, EditorInstance} from "../../../web/ts/contact/EditorFactory";


describe("ContactMeForm", () => {
    let contactMeForm: HTMLFormElement;
    let messageTextarea: HTMLTextAreaElement;
    let httpRequest: HttpRequestInterface;
    let requestHeaders: RequestHeadersInterface;
    let notify: NotifyInterface;
    let editorFactory: EditorFactory;
    let editorMock: EditorInstance;

    beforeEach(() => {
        const fragment = document.createDocumentFragment();
        contactMeForm = document.createElement("form");
        messageTextarea = document.createElement("textarea");
        messageTextarea.name = "message";
        contactMeForm.appendChild(messageTextarea);
        fragment.appendChild(contactMeForm);
        document.body.appendChild(fragment);

        httpRequest = {
            query: jest.fn(),
        } as unknown as HttpRequestInterface;

        requestHeaders = {
            jsonWithToken: jest.fn().mockReturnValue(new Headers()),
        } as unknown as RequestHeadersInterface;

        notify = {
            success: jest.fn(),
            error: jest.fn(),
        } as unknown as NotifyInterface;

        editorMock = {
            updateSourceElement: jest.fn(),
            setData: jest.fn(),
        };

        editorFactory = {
            create: jest.fn().mockResolvedValue(editorMock),
        };

        new ContactMeForm(
            contactMeForm,
            httpRequest,
            requestHeaders,
            notify,
            editorFactory
        );
    });

    afterEach(() => {
        document.body.innerHTML = "";
    });

    it("initializes the rich text editor", async () => {
        await Promise.resolve();
        expect(editorFactory.create).toHaveBeenCalledWith(messageTextarea);
    });

    it("submits invalid form", async () => {
        await Promise.resolve();
        const submitEvent = new Event("submit", {bubbles: true, cancelable: true});
        contactMeForm.reportValidity = jest.fn().mockReturnValue(false);

        contactMeForm.dispatchEvent(submitEvent);

        expect(editorMock.updateSourceElement).toHaveBeenCalled();
        expect(contactMeForm.reportValidity).toHaveBeenCalled();
        expect(httpRequest.query).not.toHaveBeenCalled();
    });

    it("submits valid form and handles response", async () => {
        await Promise.resolve();

        const submitEvent = new Event("submit", {bubbles: true, cancelable: true});
        contactMeForm.reportValidity = jest.fn().mockReturnValue(true);
        contactMeForm.reset = jest.fn();
        (httpRequest.query as jest.Mock).mockResolvedValue({message: "Success!"});

        contactMeForm.dispatchEvent(submitEvent);
        expect(editorMock.updateSourceElement).toHaveBeenCalled();
        expect(contactMeForm.reportValidity).toHaveBeenCalled();

        await Promise.resolve();

        expect(httpRequest.query).toHaveBeenCalledWith(
            "/contact/send",
            expect.objectContaining({
                method: "POST",
                body: expect.any(FormData),
                headers: expect.any(Headers),
            })
        );
        expect(notify.success).toHaveBeenCalledWith("Success!");
        expect(contactMeForm.reset).toHaveBeenCalled();
        expect(editorMock.setData).toHaveBeenCalledWith("");
    });
});

describe("ContactMeForm with failing editor", () => {
    it("shows an error if the rich text editor fails to initialize", async () => {
        const fragment = document.createDocumentFragment();
        const contactMeForm: HTMLFormElement = document.createElement("form");
        const messageTextarea: HTMLTextAreaElement = document.createElement("textarea");
        messageTextarea.name = "message";
        contactMeForm.appendChild(messageTextarea);
        fragment.appendChild(contactMeForm);
        document.body.appendChild(fragment);

        const httpRequest: HttpRequestInterface = {
            query: jest.fn(),
        } as unknown as HttpRequestInterface;

        const requestHeaders: RequestHeadersInterface = {
            jsonWithToken: jest.fn().mockReturnValue(new Headers()),
        } as unknown as RequestHeadersInterface;

        const notify: NotifyInterface = {
            success: jest.fn(),
            error: jest.fn(),
        } as unknown as NotifyInterface;

        const error = new Error("Failed to initialize editor");
        const editorFactory: EditorFactory = {
            create: jest.fn().mockRejectedValue(error),
        };

        const consoleSpy = jest.spyOn(console, "error").mockImplementation(() => {});

        new ContactMeForm(
            contactMeForm,
            httpRequest,
            requestHeaders,
            notify,
            editorFactory
        );

        await Promise.resolve();
        await Promise.resolve();

        expect(consoleSpy).toHaveBeenCalledWith(error);
        consoleSpy.mockRestore();
    });
});
