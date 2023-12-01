import applicationState from "../framework/ApplicationState.js";
import requestHeaders from "../framework/RequestHeaders.js";
import playlist from "./playlist.js";
import {DataTable} from "simple-datatables";
import WikipediaInfo from "./WikipediaInfo.js";
import {Notify} from "../framework/Notify.js";

let musicLibraryTable;

// Create a collapse instance, toggles the collapse element on invocation
const myCollapse = new bootstrap.Collapse($("#tracklistContainer"));
myCollapse.toggle();
$("#playlist-toggle").addEventListener("click", function () {
    myCollapse.toggle();
});

$$('.album-list-filter').forEach(btn => {
    btn.addEventListener('click', function () {
        const filter = this.getAttribute('data-searchtype');
        const url = '/music/' + filter;
        fetch(url, {
            headers: requestHeaders.html()
        })
            .then(response => response.text())
            .then(markup => {
                $('#music-list-container').innerHTML = markup;
                applicationState.setUrl('music', url);
                setupMusicLibraryDataTable();
            });
    });
});

function setupMusicLibraryDataTable() {
    const musicLibraryTableHTML = $('#music-library-table');

    musicLibraryTableHTML.addEventListener('click', function (event) {
        if (event.target.closest('td').classList.contains('expand')) {
            openCloseAlbumDetailView(event);
        }
        if (event.target.classList.contains('add-album')) {
            addAlbumToPlaylist(event.target.getAttribute('data-id'));
        }
        if (event.target.classList.contains('add-track')) {
            addTrackToPlaylist(event.target.getAttribute('data-id'));
        }
    });

    musicLibraryTable = new DataTable(musicLibraryTableHTML);
}

function openCloseAlbumDetailView(event) {
    const td = event.target.closest('td');
    const tr = event.target.closest('tr');
    const id = td.getAttribute('data-id');
    const artist = td.getAttribute('data-artist');
    const title = td.getAttribute('data-title');
    if (tr.nextElementSibling.classList.contains('child-row')) {
        $('#music-library-table').deleteRow(tr.rowIndex + 1);
        td.querySelector('.info-open').style.display = 'block';
        td.querySelector('.info-close').style.display = 'none';
    } else {
        showAlbumInfo(id, artist, title, tr.rowIndex);
        td.querySelector('.info-open').style.display = 'none';
        td.querySelector('.info-close').style.display = 'block';
    }
}

function addAlbumToPlaylist(albumId) {
    getAlbumJsonData(albumId).then(response => {
        if(response.album.length > 0) {
            new Notify('success',`${response.album[0].artist} - ${response.album[0].title} added to playlist`);
        } else {
            new Notify('error',`No tracks found.`);
        }
        playlist.entries.push(...response.album);
        playlist.generate();
    });
}

async function getAlbumJsonData(albumId) {
    const response = await fetch('/music/' + albumId, {headers: requestHeaders.json()});
    return await response.json();
}

function showAlbumInfo(id, artist, title, rowIndex) {
    const promise1 = getWikipediaInfo(artist, title);
    const promise2 = getAlbumInfo(id);
    Promise.allSettled([promise1, promise2]).then(data => {
        const wikipediaInfo = data[0].value;
        const trackInfo = data[1].value;
        const musicLibraryTableHTML = $('#music-library-table');
        const newRow = musicLibraryTableHTML.insertRow(rowIndex + 1);
        newRow.classList.add('child-row');
        newRow.innerHTML = `<td colspan="${musicLibraryTableHTML.rows[0].cells.length}">${trackInfo}</td>`;
        $('#wikipedia-info').innerHTML = wikipediaInfo.getExtractText() + '<br>' + wikipediaInfo.getReferenceUrl();
    });
}

async function getAlbumInfo(albumId) {
    const result = await fetch('/music/' + albumId, {headers: requestHeaders.html()});
    return await result.text();
}

async function getWikipediaInfo(artist, title) {
    const wikipediaInfo = new WikipediaInfo();
    return await wikipediaInfo.getExtract(artist, title);
}

function addTrackToPlaylist(trackId) {
    getTrackJsonData(trackId).then(response => {
        playlist.entries.push(response.track);
        playlist.generate();
    });
}

async function getTrackJsonData(trackId) {
    const response = await fetch('/track/' + trackId, {headers: requestHeaders.json()});
    return await response.json();
}

setupMusicLibraryDataTable();