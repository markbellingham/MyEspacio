import playlist from "./playlist.js";

class MusicPlayer {
    constructor() {
        this.player = $('#music-player');
        this.currentTrack = {track_id: null, track_index: null};
        this.seekBar = $('#music-seek-bar');
        this.seekBarInterval = null;
        this.trackInfo = $('#track-info');
        this.addEvents();
    }

    addEvents() {
        $('#music-player-back').addEventListener('click', this.playlistBack.bind(this));
        $('#music-player-playpause').addEventListener('click', this.playPause.bind(this));
        $('#music-player-stop').addEventListener('click', this.playerStop.bind(this));
        $('#music-player-forward').addEventListener('click', this.playlistForward.bind(this));
        this.player.addEventListener('ended', this.playlistForward.bind(this));
        this.seekBar.addEventListener('change', this.updateTrackTime.bind(this));
    }

    playlistBack() {
        if (this.currentTrack.track_id === null) return;

        if (
            this.player.currentTime > 3 ||
            this.currentTrack === playlist.entries[0]
        ) {
            this.player.currentTime = 0;
        } else {
            this.setPlayingTrack(playlist.entries[this.getCurrentTrackIndex() - 1]);
            this.playTrack();
        }
    }

    playPause() {
        if (this.isPlaying()) {
            this.player.pause();
            this.seekBarInterval = null;
            return;
        } else if (!this.currentTrack.track_id && playlist.entries.length > 0) {
            this.setPlayingTrack(playlist.entries[0]);
        }
        this.playTrack();
    }

    playTrack() {
        this.setPlayingTrack(this.currentTrack);
        this.player.play();
        this.moveSeekBar();
        playlist.generate();
    }

    isPlaying() {
        return this.player &&
            this.player.currentTime > 0 &&
            !this.player.paused &&
            !this.player.ended &&
            this.player.readyState > 2;
    }

    moveSeekBar() {
        const playerClass = this;
        this.seekBarInterval = setInterval(function () {
            playerClass.seekBar.value = (100 / playerClass.player.duration) * playerClass.player.currentTime;
        }, 1000);
    }

    playerStop() {
        this.player.pause();
        this.seekBarInterval = null;
        this.player.currentTime = 0;
        playlist.generate();
        this.trackInfo.innerHTML = '';
    }

    playlistForward() {
        if (this.currentTrack.track_id === null) return;

        const index = this.getCurrentTrackIndex();
        if (index === playlist.entries.length - 1) return;

        this.setPlayingTrack(playlist.entries[index + 1]);
        this.playTrack();
    }

    setPlayingTrack(track) {
        this.currentTrack = track;
        this.player.src = '/resources/music/' + track.filename;
        this.player.load();
        this.trackInfo.innerHTML = `
            <div class="row"><div class="col-sm-1 p-0"><img src="/resources/music/${track.image}_sm.jpg" alt="album cover"></div>
            <div class="col-sm-11 p-0"><small>${track.artist}<br>${track.track_name}</small></div></div>
        `;
    }

    getCurrentTrackIndex() {
        return playlist.entries.findIndex(track => track.track_id === this.currentTrack.track_id);
    }

    updateTrackTime() {
        this.player.currentTime = (100 / this.player.duration) * this.seekBar.value;
    }
}

const musicPlayer = new MusicPlayer();
export default musicPlayer;