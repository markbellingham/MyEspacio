import applicationState from "../framework/ApplicationState.js";

// tabs
const triggerTabList = [].slice.call($$('#main-nav a'));
triggerTabList.forEach(triggerEl => {
    const tabTrigger = new bootstrap.Tab(triggerEl);

    triggerEl.addEventListener('click', event => {
        event.preventDefault();
        tabTrigger.show();
        const tab = event.target.href.split('#')[1];
        history.pushState(null, null, applicationState.getUrl(tab));
    });
});

// Auto select the tab if the name is in the url
const urlParts = document.location.href.toString().split('/');
if (urlParts[3] !== '') {
    triggerTabList.forEach(triggerEl => {
        const href = triggerEl.getAttribute('href');
        if (href === '#' + urlParts[3]) {
            const tabTrigger = new bootstrap.Tab(triggerEl);
            tabTrigger.show();
        }
    });
}

export const searchTerm = {
    page: '',
    value: ''
};

if (urlParts[3] == 'photos') {
    searchTerm.page = 'photos';
    const searchValue = urlParts.splice(4);
    searchTerm.value = decodeURI(searchValue.join(' '));
}

if (urlParts[3] == 'login') {
    history.pushState(null, null, '/');
}