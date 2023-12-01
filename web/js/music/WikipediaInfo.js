export default class WikipediaInfo {
    constructor() {
        this.extractText = '<p>Wikipedia article not found</p>';
        this.referenceUrl = '';
    }

    extractResult(data) {
        if (data.hasOwnProperty('query')) {
            if (data.query.hasOwnProperty('pages')) {
                this.setExtractText(data.query.pages[0].extract);
                this.setReferenceUrl(data.query.pages[0].pageid);
            } else {

            }
        }
        return this;
    }

    async getExtract(artist, title) {
        const extract = await this.fetchWikiExtract(artist, title);
        return this.extractResult(extract);
    }

    async fetchWikiExtract(artist, title) {
        artist = this.cleanupQueryParam(artist);
        title = this.cleanupQueryParam(title);
        const result = await fetch(`https://en.wikipedia.org/w/api.php?formatversion=2&origin=*&action=query&prop=extracts&exintro&explaintext&format=json&generator=search&gsrnamespace=0&gsrlimit=1&gsrsearch=${artist}%20${title}`);
        return await result.json();
    }

    cleanupQueryParam(queryParam) {
        return queryParam.replace(/[^0-9A-z]/gi, ' ');
    }

    setExtractText(data) {
        this.extractText = `<p>${data}</p>`;
    }

    getExtractText() {
        return this.extractText;
    }

    setReferenceUrl(data) {
        this.referenceUrl = `<small>Extract taken from <a href="https://en.wikipedia.org/?curid=${data}">Wikipedia</a></small>`;
    }

    getReferenceUrl() {
        return this.referenceUrl;
    }
}