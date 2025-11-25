const editorMock = {
    updateSourceElement: jest.fn(),
    setData: jest.fn(),
};

jest.mock("@ckeditor/ckeditor5-build-classic", () => ({
    create: jest.fn().mockResolvedValue(editorMock),
}));

import ckEditorAdapter from "../../../web/ts/contact/ckEditorAdapter";
import ClassicEditor from "@ckeditor/ckeditor5-build-classic";

test("init sets up the editor", async () => {
    const textArea = document.createElement("textarea");

    await ckEditorAdapter.create(textArea);

    expect(ClassicEditor.create).toHaveBeenCalledWith(textArea, {
        licenseKey: "GPL",
    });
});

test("setData resets the editors data", async () => {
    const textArea = document.createElement("textarea");
    const adapter = await ckEditorAdapter.create(textArea);

    adapter.setData("");

    expect(editorMock.setData).toHaveBeenCalledWith("");
});

test("updateSourceElement updates the source element", async () => {
    const textArea = document.createElement("textarea");
    const adapter = await ckEditorAdapter.create(textArea);

    adapter.updateSourceElement();

    expect(editorMock.updateSourceElement).toHaveBeenCalled();
});
