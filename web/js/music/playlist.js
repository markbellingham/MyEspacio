import musicPlayer from "./player.js";
import {addDynamicEventListener} from "../framework/dynamicListener.js";

class PlayList {
    constructor() {
        this.entries = [];
        this.trackListElement = document.querySelector('#track-list');
        this.playerContainer = document.querySelector('#player-container');
        this.playlistToggle = document.querySelector('#playlist-toggle');
        this.tracklistContainer = document.querySelector('#tracklistContainer');
        this.lyricsContainer = document.querySelector('#lyrics');
        this.playlistLength = document.querySelector('#playlist-length');
        this.addEvents();
        this.generate();
    }

    addEvents() {
        this.playlistToggle.addEventListener('click', this.show.bind(this));
        addDynamicEventListener(this.trackListElement, 'click', '.remove', this.removeTrack.bind(this));
        addDynamicEventListener(this.trackListElement, 'dblclick', 'tr', this.setPlayingTrack.bind(this));
        document.querySelector('#clear-playlist').addEventListener('click', this.clear.bind(this));
    }

    show(event) {
        if (event.target.classList.contains('bi-chevron-down')) {
            this.switchChevrons(event.target, 'down', 'up');
            // this.tracklistContainer.classList.add('open');
            // this.trackListElement.classList.add('open');
            // this.lyricsContainer.classList.add('open');
            // this.playerContainer.classList.add('open');
            this.tracklistContainer.style.height = '100%';
            this.tracklistContainer.style['box-shadow'] = '0 8px 8px 0 rgba(0, 0, 0, 0.2), 0 10px 20px 0 rgba(0, 0, 0, 0.19)';
            playlist.trackListElement.style.overflow = 'auto';
            playlist.trackListElement.style['max-height'] = (window.innerHeight - 220);
            this.lyricsContainer.style.overflow = 'auto';
            this.lyricsContainer.style['max-height'] = (window.innerHeight - 220);
            this.playerContainer.style['box-shadow'] = '0 8px 8px 0 rgba(0, 0, 0, 0.2), 0 10px 20px 0 rgba(0, 0, 0, 0.19)';
        } else {
            const playlistClass = this;
            setTimeout(function () {
                playlistClass.switchChevrons(event.target, 'up', 'down');
                // playlistClass.tracklistContainer.classList.remove('open');
                // playlistClass.trackListElement.classList.remove('open');
                // playlistClass.lyricsContainer.classList.remove('open');
                // playlistClass.playerContainer.classList.remove('open');
                playlistClass.playerContainer.removeAttribute('style');
            }, 250);

        }
    }

    switchChevrons(t, remove, add) {
        t.classList.remove(`bi-chevron-${remove}`);
        t.classList.add(`bi-chevron-${add}`);
    }

    setPlayingTrack(event) {
        const tr = event.target.closest('tr');
        const trackId = parseInt(tr.id.substring(2));
        const track = this.entries.find(t => t.track_id === trackId);
        if (track) {
            musicPlayer.setPlayingTrack(track);
            musicPlayer.playTrack();
            this.generate();
        }
    }

    removeTrack(event) {
        const trackId = parseInt(event.target.getAttribute('data-id'));
        this.entries.splice(this.entries.findIndex(t => t.track_id === trackId), 1);
        this.generate();
    }

    clear() {
        this.entries = [];
        this.generate();
    }

    generate() {
        let totalDuration = 0;
        let markup = `<table class="mt-1" style="width: 100%;">`;
        if (this.entries.length > 0) {
            for (let [i, track] of this.entries.entries()) {
                console.log(track);
                totalDuration += parseInt(track.duration ??= '0');
                const colour = musicPlayer.currentTrack.track_id === track.track_id ? 'text-danger' : '';
                const top50Marker = track.top50 > 0 ? `<i class="bi bi-star-fill text-warning" title="Top 50"></i>` : '';
                markup += `
                <tr class="${colour}" id="t-${track.track_id}">
                    <td>${(i + 1).toString().padStart(2, '0')}</td>
                    <td>${track.track_name} ${top50Marker}</td>
                    <td>${this.formatDuration(track.duration)}</td>
                    <td><button class="btn btn-xs btn-danger remove" data-id="${track.track_id}" title="Remove">x</button></td>
                </tr>`;
            }
        } else {
            markup += `<tr><td class="text-center"><h5>Playlist Is Empty</h5></td></tr>`;
        }
        markup += `</table>`;
        this.trackListElement.innerHTML = markup;
        this.playlistLength.innerHTML = ` [${this.formatDuration(totalDuration)}]`;
    }

    /**
     * Convert seconds into minutes and seconds
     * @param { int } duration
     * @returns {string}
     */
    formatDuration(duration) {
        const d = this.calculateDuration(duration);
        return `${d.hours}${d.minutes}m ${d.seconds}s`;
    }

    /**
     * @param { int } duration
     * @returns {{hours: string, seconds: string, minutes: number}}
     */
    calculateDuration(duration) {
        let hours = '';
        let minutes = parseInt((duration / 60).toString());
        if (minutes > 60) {
            hours = parseInt((minutes / 60).toString()) + 'h ';
            minutes = (minutes % 60).toString().padStart(2, '0');
        }
        const seconds = (duration % 60).toString().padStart(2, '0');
        return {hours, minutes, seconds};
    }
}

const playlist = new PlayList();
export default playlist;
