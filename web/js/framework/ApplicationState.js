class ApplicationState {
    constructor() {
        this.url = {
            home: '/',
            music: '/music',
            photos: '/photos',
            blog: '/blog',
            games: '/games',
            contact: '/contact'
        };
        this.selectedGame = null;
    }

    getUrl(tab) {
        return this.url[tab];
    }

    setUrl(tab, url) {
        this.url[tab] = url;
        history.pushState(null, null, url);
    }

    getSelectedGame() {
        return this.selectedGame;
    }

    setSelectedGame(game) {
        this.selectedGame = game;
    }
}

const applicationState = new ApplicationState();
export default applicationState;