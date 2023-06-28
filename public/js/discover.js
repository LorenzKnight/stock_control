// Get elements
const playerBtns = document.getElementById('player-btns');
const playingNow = document.getElementById('playing-now');
const volumeSlider = document.getElementById('volume-slider');
const volumeValue = document.getElementById('volume-value');
const searchField = document.getElementById('search-field');
const songList = document.getElementById('song-list');
const artistHeading = document.getElementById('song-list-artist-heading')
const equalizer = document.getElementById('equalizer');

playerBtns.addEventListener('click', playerFunc);

//Global variables
let soundPlaying = false;
let songPlayingNow = ''
let lastPlayed = ''
let fetchedData;
let volumeNumber = 0.5;
let songQueue = [];
let activeSongQueueIndex;
let isFooterToggled = false;
let isHeaderNavToggled = false;
let sound;


let musicList = document.querySelectorAll('.song-list');

musicList.forEach((element)=>{
    element.addEventListener('click', getSongId);
});

function getSongId(event) {
    var song = {
        songId: event.target.getAttribute('data-id'),
        songName: event.target.getAttribute('data-song'),
        fileName: event.target.getAttribute('data-file')
    };
    console.log(song);
    changeSong(event, song, null);
};

function changeSong(e, songToPlay, queueIndex) {
    equalizer.classList.remove('hidden')
    if (songToPlay) {
        console.log(songToPlay.songName)
        if (sound) {
            sound.pause();
            sound.currentTime = 0;
        }      
        sound = new Audio(`audio/${songToPlay.fileName}`);
        activeSongQueueIndex = queueIndex;
        sound.volume = volumeNumber;
        sound.play();
        soundPlaying = true;
        playingNow.innerText = `Playing now: ${songToPlay.songName}`
    } else if (!soundPlaying) {
        songPlayingNow = e.target.innerText;
        sound = new Audio(`audio/${e.target.dataset.fileName}`);
        activeSongQueueIndex = e.target.dataset.queueIndex;
        sound.volume = volumeNumber;
        sound.play();
        soundPlaying = true;
        playingNow.innerText = `Playing now: ${songPlayingNow}`
    } else if (soundPlaying) {
        sound.pause();
        sound.currentTime = 0;
        sound = new Audio(`audio/${e.target.dataset.fileName}`);
        activeSongQueueIndex = e.target.dataset.queueIndex;
        sound.volume = volumeNumber;
        sound.play();
        soundPlaying = true;
        playingNow.innerText = `Playing now: ${songPlayingNow}`
    }
}
  
function playerFunc(e) {
    console.log(songList)
    console.log(e.target.dataset.btn)
    if (soundPlaying) {
        switch (e.target.dataset.btn) {
        case "play":
            sound.play();
            playingNow.innerText = `Playing now: ${songPlayingNow}`
            equalizer.classList.remove('hidden')
            break;
        case "pause":
            sound.pause();
            playingNow.innerText = `Pausad: ${songPlayingNow}`
            equalizer.classList.add('hidden')
            break;
        case "stop":
            sound.pause();
            playingNow.innerText = `Stoppad: ${songPlayingNow}`
            sound.currentTime = 0;
            equalizer.classList.add('hidden')
            break;
        case "backward":
            if (activeSongQueueIndex > 0) {
            activeSongQueueIndex--;
            changeSong(e, songQueue[activeSongQueueIndex], activeSongQueueIndex)
            } else {
            return
            }
            break;
        case "forward":
            if (activeSongQueueIndex < songQueue.length - 1) {
            activeSongQueueIndex++;
            changeSong(e, songQueue[activeSongQueueIndex], activeSongQueueIndex)
            } else {
            return
            }
            break;
        }
    } else {
        return
    }
}

volumeSlider.oninput = function () {
    console.log(this.value);
    const volume = this.value * 0.01;
    console.log(volume)
    if (soundPlaying) {
      sound.volume = volume;
      volumeValue.innerText = Math.floor(volume * 100);
    } else {
      volumeValue.innerText = Math.floor(volume * 100);
      volumeNumber = volume;
      return;
    }
}