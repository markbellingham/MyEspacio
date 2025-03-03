import applicationState from "../framework/ApplicationState.js";

document.querySelector('#game-selector').addEventListener('change', function () {
    const code = this.value;
    const url = code == '' ? '/games' : '/games/' + code;
    applicationState.setUrl('games', url);
    fetch(`/games/${code}/index.html`)
        .then(response => response.text())
        .then(markup => {
            document.querySelector('#selected-game').innerHTML = markup;
        });
    addCssLink(code);
    addJavascriptLink(code);
});

function addCssLink(code) {
    const css = document.createElement('link');
    css.setAttribute('type', 'text/css');
    css.setAttribute('href', `/games/${code}/${code}-style.css`);
    css.setAttribute('rel', 'stylesheet');
    console.log(css);
    document.getElementsByTagName('head')[0].appendChild(css);
}

function addJavascriptLink(code) {
    const script = document.createElement('script');
    script.type = 'text/javascript';
    script.src = `/games/${code}/${code}-app.js?t=${randomParameter()}`;
    document.head.appendChild(script);
}

function randomParameter() {
    return Math.random().toString(36).replace(/[^a-z]+/g, '');
}
