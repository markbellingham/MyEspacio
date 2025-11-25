import {EditorFactory} from "./EditorFactory";
import ClassicEditor from "@ckeditor/ckeditor5-build-classic";

const ckEditorAdapter: EditorFactory = {
    async create(element: HTMLTextAreaElement) {
        const editor = await ClassicEditor.create(element, {
            licenseKey: "GPL"
        });

        return {
            updateSourceElement() {
                editor.updateSourceElement();
            },
            setData(s: string) {
                editor.setData(s);
            },
        };
    }
};

export default ckEditorAdapter;
