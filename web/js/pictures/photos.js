import applicationState from "../framework/ApplicationState.js";
import {addDynamicEventListener} from "../framework/dynamicListener.js";
import requestHeaders from "../framework/RequestHeaders.js";
import {Notify} from "../framework/Notify.js";
import tooltips from "../framework/Tooltips.js";
import {searchTerm} from "../frontpage/frontpage";

const commonSearches = [
    'most-popular',
    'my-favourites'
];

if (searchTerm.page == 'photos' && isAlbum(searchTerm.value)) {
    $('#photo-album-select').value = searchTerm.value;
} else if (commonSearches.indexOf(searchTerm.value) > -1) {
    $(`#${searchTerm.value}-photos-btn`).checked = true;
} else {
    $('#photo-search-btn').checked = true;
    $('#photo-search-input').value = searchTerm.value;
}

let photoSearchTimeout;
const userFaves = JSON.parse(localStorage.getItem('photo_faves')) || [];

$('#photo-search-input').addEventListener('keyup', function () {
    clearTimeout(photoSearchTimeout);
    const input = this;
    photoSearchTimeout = setTimeout(function () {
        const searchValue = input.value;
        const photoAlbum = $('#photo-album-select').value;
        searchPhotos(searchValue, photoAlbum);
    }, 500);
});

$('#photo-search-btn').addEventListener('click', function () {
    const searchValue = $('#photo-search-input').value;
    const photoAlbum = $('#photo-album-select').value;
    searchPhotos(searchValue, photoAlbum);
});

$('#photos').addEventListener('click', function (event) {
    if (event.target.tagName === 'IMG') {
        const photoId = event.target.getAttribute('data-id');
        fetch('/photo/' + photoId, {
            headers: requestHeaders.html()
        })
            .then(response => response.text())
            .then(data => {
                if (data.toLowerCase().startsWith('<!doctype')) {
                    $('html').innerHTML = data;
                } else {
                    $('#modals').innerHTML = data;
                    const modal = new bootstrap.Modal($('#modalIMG'));
                    modal.show();
                    $('#full-size-photo').addEventListener('click', openFullSizePhoto);
                    if (userFaves.indexOf(photoId) > -1) {
                        showAsFavourite($('#add-photo-fave'));
                    } else {
                        setAddFaveEventListener($('#add-photo-fave'));
                    }
                }
            });
    }
});

$('#most-popular-photos-btn').addEventListener('click', function () {
    fetch('/photos/most-popular', {
        headers: requestHeaders.html()
    })
        .then(response => response.text())
        .then(response => {
            if (response.toLowerCase().startsWith('<!doctype')) {
                document.innerHTML = response;
            } else {
                $('#photo-grid').innerHTML = response;
            }
            $('#photo-album-select').value = '';
            history.pushState(null, null, '/photos/most-popular');
        });
});

addDynamicEventListener($('#modals'), 'click', '#photo-comment-submit', submitComment);

function searchPhotos(searchValue, photoAlbum) {
    let url = '/photos';
    url += photoAlbum == '' ? '' : '/' + photoAlbum;
    url += searchValue == '' ? '' : '/' + searchValue;
    applicationState.setUrl('photos', url);
    fetch(url, {
        headers: requestHeaders.html()
    })
        .then(response => response.text())
        .then(response => {
            if (response.toLowerCase().startsWith('<!doctype')) {
                document.innerHTML = response;
            } else {
                $('#photo-grid').innerHTML = response;
            }
        });
}

function submitComment(event) {
    const form = event.delegatedTarget.closest('form');
    if (form.reportValidity()) {
        let formData = new FormData(form);
        formData = Object.fromEntries(formData.entries());
        fetch(`/photo/${formData.id}/comment`, {
            credentials: 'same-origin',
            headers: requestHeaders.jsonWithToken(),
            method: 'POST',
            body: JSON.stringify(formData)
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    new Notify('success', 'Comment added');
                    form.reset();
                } else {
                    new Notify('danger', `Sorry, there was a problem adding your comment.<br>${data.message}`);
                }
                $('#photo-comments').innerHTML = data.comments;
            });
    }
}

function openFullSizePhoto() {
    const url = $('#modal-image-container img').getAttribute('src');
    window.open(url);
}

function addPhotoFave() {
    const heart = this;
    const photoId = heart.getAttribute('data-id');
    fetch(`/photo/${photoId}/fave`, {
        credentials: 'same-origin',
        headers: requestHeaders.jsonWithToken(),
        method: 'POST',
    })
        .then(response => response.json())
        .then(data => {
            if (data.success == true) {
                showAsFavourite(heart);
                addToFavouritesList(photoId);
                $('#fave-count').innerText = data.faveCount;
            } else {
                new Notify('error', data.message);
            }
            getPhotoComments(photoId);
        });
}

function getPhotoComments(photoId) {
    fetch(`/photo/${photoId}/comments`, {
        headers: requestHeaders.html(),
    })
        .then(response => response.text())
        .then(response => {
            $('#photo-comments').innerHTML = response;
        });
}

function setAddFaveEventListener(heart)
{
    heart.addEventListener('click', addPhotoFave);
    setTimeout(function() {
        tooltips.show();
    },0);
}

function showAsFavourite(heart)
{
    heart.classList.remove('bi-heart');
    heart.classList.add('bi-heart-fill', 'text-danger');
    heart.removeAttribute('title');
    heart.removeAttribute('data-bs-toggle');
}

function addToFavouritesList(photoId)
{
    userFaves.push(photoId);
    localStorage.setItem('photo_faves', JSON.stringify(userFaves));
}

function isAlbum(searchTerm)
{
    let option = $('#photo-album-select').querySelector(`[value="${searchTerm}"]`);
    return Boolean(option);
}