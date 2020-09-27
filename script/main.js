const playerBtns = document.getElementById('player-btns');
const playingNow = document.getElementById('playing-now');
const songList = document.getElementById('artist-list');
const volumeSlider = document.getElementById('volume-slider');
const volumeValue = document.getElementById('volume-value');
const searchField = document.getElementById('search-field');
const realSongList = document.getElementById('song-list');
const artistHeading = document.getElementById('artist-list-artist-heading')
searchField.addEventListener('input', handleSearchInput);
playerBtns.addEventListener('click', playerFunc);
let soundPlaying = false;
let songPlayingNow = ''
let lastPlayed = ''
let fetchedData;
let volumeNumber = 0.5;

function fetchData() {
  fetch("../api/api.JSON")
    .then(res => res.json())
    .then(data => {
      fetchedData = data;
      for (let i = 0; i < fetchedData.length; i++) {
        console.log(fetchedData[i].artist)
        let listElement = document.createElement("li");
        let titleElement = document.createElement("h3");
        titleElement.innerText = fetchedData[i].artist;
        titleElement.dataset.artistID = i;
        listElement.appendChild(titleElement)
        songList.appendChild(listElement);
      }
      songList.addEventListener('click', renderSongList)
    }
    )
}



function renderSongList(e, arrToRender) {
  if (arrToRender) {
    for (let i = 0; i < arrToRender.length; i++) {
      const listElement = document.createElement("li");
      const titleElement = document.createElement("h3");
      titleElement.innerText = arrToRender[i].songName;
      titleElement.dataset.fileName = arrToRender[i].fileName;
      listElement.appendChild(titleElement)
      arrToRender[i].fileName ? listElement.addEventListener('click', changeSong) : null;
      realSongList.appendChild(listElement);
    }
  } else if (!arrToRender) {
    const artistId = e.target.dataset.artistID
    const artist = fetchedData[artistId].artist;

    artistHeading.innerText = artist;
    realSongList.innerHTML = '';

    for (let i = 0; i < fetchedData[artistId].songs.length; i++) {
      const listElement = document.createElement("li");
      const titleElement = document.createElement("h3");
      titleElement.innerText = fetchedData[artistId].songs[i].songName;
      titleElement.dataset.fileName = fetchedData[artistId].songs[i].fileName;
      listElement.appendChild(titleElement)
      listElement.addEventListener('click', changeSong)
      realSongList.appendChild(listElement);
    }
  }


}


function changeSong(e) {
  songPlayingNow = e.target.innerText;
  if (!soundPlaying) {
    sound = new Audio(`../audio/${e.target.dataset.fileName}`);
    sound.volume = volumeNumber;
    sound.play();
    soundPlaying = true;
    playingNow.innerText = `Spelar nu: ${songPlayingNow}`
  } else if (soundPlaying) {
    sound.pause();
    sound.currentTime = 0;
    sound = new Audio(`../audio/${e.target.dataset.fileName}`);
    sound.volume = volumeNumber;
    sound.play();
    soundPlaying = true;
    playingNow.innerText = `Spelar nu: ${songPlayingNow}`
  }
}

function playerFunc(e) {
  console.log(e.target.dataset.btn)
  if (soundPlaying) {
    switch (e.target.dataset.btn) {
      case "play":
        sound.play();
        playingNow.innerText = `Spelar nu: ${songPlayingNow}`
        break;
      case "pause":
        sound.pause();
        playingNow.innerText = `Pausad: ${songPlayingNow}`
        break;
      case "stop":
        sound.pause();
        playingNow.innerText = `Stoppad: ${songPlayingNow}`
        sound.currentTime = 0;
    }
  } else {
    return
  }
}

function handleSearchInput(e) {
  console.log(e.target.focus)
  realSongList.innerHTML = '';
  artistHeading.innerText = 'Sökresultat:';
  if (e.target.value.length < 1) {
    realSongList.innerHTML = '';
    renderSongList(e, [{ songName: 'Nothing here! Try searching or exploring your artists! :)', fileName: false }])
    return
  } else {
    let searchResults = [];
    for (let i = 0; i < fetchedData.length; i++) {
      searchResults = searchResults.concat(fetchedData[i].songs.filter(obj => Object.keys(obj).some(key => obj[key].toLowerCase().includes(e.target.value))))
    }
    renderSongList(e, searchResults)
  }
}
searchField.onblur = () => searchField.value = '';
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



window.onload = fetchData();