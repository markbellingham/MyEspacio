```dataviewjs
const rootFolder = ""; // Root folder ("" for the whole vault, or "docs/" for a specific folder)
const ignoreFolders = ["Templates"]; // List of folders to ignore

function renderToC(folder, container) {
    // Get all pages in the folder
    const files = dv.pages(`"${folder}"`)
        .where(p => !ignoreFolders.some(ignored => p.file.folder.includes(ignored)));

    // Get subfolders
    const subfolders = [...new Set(files.map(f => f.file.folder))]
        .filter(subfolder => subfolder.startsWith(folder) && subfolder !== folder);

    // Create a list element for the current folder
    const list = container.createEl("ul");

    // Add subfolders to the list
    subfolders.forEach(subfolder => {
        const listItem = list.createEl("li", { text: subfolder.split("/").pop() }); // Folder name
        renderToC(subfolder, listItem); // Recursively render nested folders
    });

    // Add files in the current folder
    files
        .where(file => file.file.folder === folder)
        .forEach(file => {
            list.createEl("li").createEl("a", {
                text: file.file.name,
                href: file.file.path
            });
        });
}

// Start rendering ToC in the current note
const container = dv.container; // Get the note's container
renderToC(rootFolder, container);
```
