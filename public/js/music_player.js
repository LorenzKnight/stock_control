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
    var song = {
        songId: element.getAttribute('data-id'),
        songName: element.getAttribute('data-song'),
        fileName: element.getAttribute('data-file'),
        songIndex: element.getAttribute('data-queue-index')
    };
    songQueue.push(song);
});

var activeSongIndex = 0;

function getSongId(event) {
    console.log('hej hej hej');
    var song = {
        songId: event.target.getAttribute('data-id'),
        songName: event.target.getAttribute('data-song'),
        fileName: event.target.getAttribute('data-file'),
        songIndex: event.target.getAttribute('data-queue-index')
    };
    console.log(song);
    changeSong(event, song, song.songIndex);
};

function changeSong(e, songToPlay, queueIndex) {
    equalizer.classList.remove('hidden')
    if(queueIndex == 0) {
        // console.log(document.querySelector('.fa-step-forward'));
        document.querySelector('.fa-step-forward').setAttribute('disabled', true);
    } else {
        // console.log(document.querySelector('.fa-step-forward'));
        document.querySelector('.fa-step-forward').setAttribute('disabled', false);
    }

    if(queueIndex == songQueue.length - 1) {
        // console.log(document.querySelector('.fa-step-forward'));
        document.querySelector('.fa-step-forward').setAttribute('disabled', true);
    } else {
        // console.log(document.querySelector('.fa-step-forward'));
        document.querySelector('.fa-step-forward').setAttribute('disabled', false);
    }

    if (songToPlay) {
        console.log(songToPlay.songName)
        if (sound) {
            sound.pause();
            sound.currentTime = 0;
        }      
        sound = new Audio(`audio/${songToPlay.fileName}`);
        activeSongIndex = queueIndex;
        sound.volume = volumeNumber;
        sound.play();
        soundPlaying = true;
        playingNow.innerText = `Playing now: ${songToPlay.songName}`
    } else if (!soundPlaying) {
        songPlayingNow = e.target.innerText;
        sound = new Audio(`audio/${e.target.dataset.fileName}`);
        activeSongIndex = e.target.dataset.queueIndex;
        sound.volume = volumeNumber;
        sound.play();
        soundPlaying = true;
        playingNow.innerText = `Playing now: ${songPlayingNow}`
    } else if (soundPlaying) {
        sound.pause();
        sound.currentTime = 0;
        sound = new Audio(`audio/${e.target.dataset.fileName}`);
        activeSongIndex = e.target.dataset.queueIndex;
        sound.volume = volumeNumber;
        sound.play();
        soundPlaying = true;
        playingNow.innerText = `Playing now: ${songPlayingNow}`
    }
}
  
function playerFunc(e) {
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
            if (activeSongIndex > 0) {
                activeSongIndex--;
                changeSong(e, songQueue[activeSongIndex], activeSongIndex)
            } else {
                return
            }
            break;
        case "forward":
            if (activeSongIndex < songQueue.length - 1) {
                activeSongIndex++;
                changeSong(e, songQueue[activeSongIndex], activeSongIndex)
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