export interface EditorFactory {
    create(element: HTMLTextAreaElement): Promise<EditorInstance>
}

export interface EditorInstance {
    updateSourceElement(): void;
    setData(s: string): void;
}
