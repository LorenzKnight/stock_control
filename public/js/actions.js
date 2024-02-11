document.addEventListener("DOMContentLoaded", () => {
	// console.log(currentUser);
    var profileTrigger = document.getElementById('profileTrigger');
    
	if (profileTrigger) {
		profileTrigger.addEventListener('click', function() {
			var profileDropdown = document.getElementById('profileDropdown');
			if (profileDropdown) {
				profileDropdown.style.display = profileDropdown.style.display === 'none' ? 'block' : 'none';
			}
		});

		document.addEventListener('click', function(event) {
			var isClickInsideMenu = profileTrigger.contains(event.target) || profileDropdown.contains(event.target);
			if (!isClickInsideMenu) {
				profileDropdown.style.display = 'none';
			}
		});
	}


	const urlParams = new URLSearchParams(window.location.search);
    const params = {
        list: urlParams.get('list'),
        uploader: urlParams.get('uploader'),
        // Puedes agregar más variables aquí
    };

    const album = document.getElementById('album');
    const listing = document.getElementById('listing');
    const uploaderContainer = document.getElementById('uploader-container');

    let state = 'album'; // Estado por defecto
    for (let param in params) {
        if (params[param]) {
            state = param;
            break;
        }
    }

    switch (state) {
        case 'uploader':
            uploaderContainer.classList.remove('hidden');
            break;
        case 'list':
            listing.classList.remove('hidden');
            break;
        default:
            album.classList.remove('hidden');
            break;
    }

    let bgOverlayer = document.querySelector('.bg-overlayer');
    bgOverlayer.addEventListener('click', function(){
        closeMiniMenu()
    });

    let addPlaylist = document.querySelectorAll('.addPlaylist');
    addPlaylist.forEach(function(element){
        element.addEventListener('click', function(){
            let songId = element.getAttribute('data-songId');
            add_to_list(songId);
        });
    });

	let removeFromPlaylis = document.querySelectorAll('.removeFromPlaylis');
	removeFromPlaylis.forEach(function(element){
		element.addEventListener('click', function(){
            let removeId = element.getAttribute('data-removeId');
            confirm_remove(removeId);
        });
	});

    let playlistContainer = document.querySelectorAll('.playlistContainer');
    playlistContainer.forEach(function(element){
        element.addEventListener('click', function(){
            let listId = element.getAttribute('data-playlistid');
            let formular_songs_list = document.getElementById('formular_songs_list');
            let songId = formular_songs_list.getAttribute('data-songId');
            console.log(listId+', '+songId);

            var xmlhttp = new XMLHttpRequest();
            xmlhttp.onreadystatechange = function() {
                if (this.readyState == 4 && this.status == 200) {
					var response = JSON.parse(this.responseText);
                    console.log(response);
                    close_popup()

					const statusMessageElem = document.getElementById('status-message');
					if (statusMessageElem) {
						statusMessageElem.innerText = response.message;
						statusMessageElem.style.display = 'block';

						setTimeout(() => {
							statusMessageElem.style.opacity = '0';
							setTimeout(() => {
								statusMessageElem.style.display = 'none';
								statusMessageElem.style.opacity = '0.9';
							}, 1000);
						}, 2000);
					}

					setTimeout(() => {
						window.location.reload();
					}, 2100);
                }
            }
            var formData = new FormData(); 
            formData.append('MM_insert', 'addToList');
            formData.append('songId', songId);
            formData.append('listId', listId);
        
            xmlhttp.open("POST", "logic/discover_be.php", true);
            xmlhttp.send(formData);
        });
    });

    let searchField = document.getElementById('searchField');
    searchField.addEventListener('keyup', search);
    function search(event) {
        var searching = event.target.value.toLowerCase();
        console.log(searching);
        var resultContainer = document.getElementById('main-content');
        var wrappers = document.getElementsByClassName('wrapper-home');

        if (searching.trim() === '') {
            for (var i = 0; i < wrappers.length; i++) {
                wrappers[i].style.display = '';
            }
            resultContainer.innerHTML = '';
            resultContainer.style.height = 'auto';
            return;
        }
        
        var xmlhttp = new XMLHttpRequest();
        xmlhttp.onreadystatechange = function() {
            if (this.readyState == 4 && this.status == 200) {
                var response = this.responseText;
                response = JSON.parse(response);

                console.log(response);

                if (response.length === 0) {
					for (var i = 0; i < wrappers.length; i++) {
                        wrappers[i].style.display = 'none';
                    }

                    resultContainer.innerHTML = `<table cellspacing="0" width="100%" height="50px"><tr><td align="center">There are no results to show</td></tr></table>
												<table cellspacing="0" width="100%" height="50px"><tr><td align="center"><button>upload here</button></td></tr></table>`;
					var resultContainerStyles = {
						height: '94.9vh',
						fontSize: '14px',
						color: 'gray'
					};
                      
 					Object.keys(resultContainerStyles).forEach(function(key) {
						resultContainer.style[key] = resultContainerStyles[key];
					});
                } else if (Array.isArray(response)) {
                    // Generar la tabla HTML con los resultados
                    var table = document.createElement('table');
                        table.className = 'music-list';
                        table.setAttribute('cellspacing', 0);
        
                    response.forEach(function(song) {
                        var row = document.createElement('tr');

                        var songCoverCell = document.createElement('td');
                        var songCoverDiv = document.createElement('div');
                            songCoverDiv.className = 'songs-cover';

                        var songCoverImg = document.createElement('img');
							songCoverImg.src = !song['cover'] || song['cover'] === '' ? 'images/profile/' + song.userData.image : 'images/cover/' + song.cover;
                            songCoverImg.alt = '';

                            songCoverDiv.appendChild(songCoverImg);
                            songCoverCell.appendChild(songCoverDiv);
        
                        var artistCell = document.createElement('td');
                            artistCell.className = 'artist-container';
                        	artistCell.textContent = (song.artist + ' - ' + song.song_name)
							.split(' ')
							.map(word => word[0].toUpperCase() + word.substring(1).toLowerCase())
							.join(' ');
                            artistCell.setAttribute('data-queue-index', 0);
                            artistCell.setAttribute('data-id', song.sid);
                            artistCell.setAttribute('data-song', song.song_name);
                            artistCell.setAttribute('data-file', song.file_name);
        
                        var songNameCell = document.createElement('td');
                            songNameCell.setAttribute('width', '5%');
                            
                        var actionsBtn = document.createElement('button');
                            actionsBtn.className = 'actions-btn';
                            actionsBtn.setAttribute('data-menu', song.sid);
                            actionsBtn.textContent = 'ooo';
                            songNameCell.appendChild(actionsBtn);

                        var songActionsDiv = document.createElement('div');
                            songActionsDiv.className = 'song-actions';
                            songActionsDiv.setAttribute('id', 'song-actions');

                        var ul = document.createElement('ul');
                        var li1 = document.createElement('li');
                            li1.textContent = 'Add playlist';
                                li1.className = 'addPlaylist';
                                li1.setAttribute('data-songId', song.sid);
                        var li2 = document.createElement('li');
                            li2.textContent = 'Action 2';
                        var li3 = document.createElement('li');
                            li3.textContent = 'Action 3';

                            ul.appendChild(li1);
                            ul.appendChild(li2);
                            ul.appendChild(li3);
                            songActionsDiv.appendChild(ul);
                            songNameCell.appendChild(songActionsDiv);

                        row.appendChild(songCoverCell);
                        row.appendChild(artistCell);
                        row.appendChild(songNameCell);

                        table.appendChild(row);
                    });

                    let artistContainer = table.querySelectorAll('.artist-container');
                    artistContainer.forEach((element)=>{
                        element.addEventListener('click', getSongId);
                    });
        
                    // Limpia los wrapper para mostrar los resultados la busqueda
                    resultContainer.innerHTML = '';
                    resultContainer.style.height = '94.9vh';
                    for (var i = 0; i < wrappers.length; i++) {
                        wrappers[i].style.display = 'none';
                    }
                    
                    resultContainer.appendChild(table);

                    let menuBtn = document.querySelectorAll('.actions-btn');

                    menuBtn.forEach((element)=>{
                        element.addEventListener('click', pullDownMenu);
                    });

                    let addPlaylist = document.querySelectorAll('.addPlaylist');
                    addPlaylist.forEach(function(element){
                        element.addEventListener('click', function(){
                            let songId = element.getAttribute('data-songId');
                            add_to_list(songId);
                        });
                    });
                } else {
                    console.log('The response is not a valid array:', response);
                }
            }
        };
        var formData = new FormData(); 
        formData.append('MM_insert', 'formsearch');
        formData.append('searching', searching);
    
        xmlhttp.open("POST", "logic/discover_be.php", true);
        xmlhttp.send(formData);
    }

	let buttonAddPlaylist = document.getElementById('button-add-playlist');
    buttonAddPlaylist.addEventListener('click', function(){
		let clicCreateList = document.getElementById('clic-create-list');
		let inputList = document.getElementById('input-list');

		clicCreateList.style.display = 'none';
		inputList.style.display = 'block';
    });

	let buttonAddCreate = document.getElementById('button-add-create');
	buttonAddCreate.addEventListener('click', function(){
		let songId = formular_songs_list.getAttribute('data-songId');
		let inputPlaylist = document.getElementById('input-playlist').value;

		var xmlhttp = new XMLHttpRequest();
		xmlhttp.onreadystatechange = function() {
			if (this.readyState == 4 && this.status == 200) {
				var response = JSON.parse(this.responseText);
			console.log(response);
				close_popup()

				const statusMessageElem = document.getElementById('status-message');
				if (statusMessageElem) {
					statusMessageElem.innerText = response.message;
					statusMessageElem.style.display = 'block';

					setTimeout(() => {
						statusMessageElem.style.opacity = '0';
						setTimeout(() => {
							statusMessageElem.style.display = 'none';
							statusMessageElem.style.opacity = '0.9';
						}, 1000);
					}, 2000);
				}

				setTimeout(() => {
					window.location.href = 'discover';
				}, 3000);
			}
		}
		var formData = new FormData(); 
		formData.append('MM_insert', 'createAndAdd');
		formData.append('songId', songId);
		formData.append('inputPlaylist', inputPlaylist);
	
		xmlhttp.open("POST", "logic/discover_be.php", true);
		xmlhttp.send(formData);
	});

	let confirmBtn = document.getElementById('confirm-btn');
	confirmBtn.addEventListener('click', function(){
		let formular_remove_song = document.getElementById('formular_remove_song');
		let songId = formular_remove_song.getAttribute('data-removeId');

		var xmlhttp = new XMLHttpRequest();
		xmlhttp.onreadystatechange = function() {
			if (this.readyState == 4 && this.status == 200) {
				var response = JSON.parse(this.responseText);
				console.log(response);
				close_popup()

				const statusMessageElem = document.getElementById('status-message');
				if (statusMessageElem) {
					statusMessageElem.innerText = response.message;
					statusMessageElem.style.display = 'block';

					setTimeout(() => {
						statusMessageElem.style.opacity = '0';
						setTimeout(() => {
							statusMessageElem.style.display = 'none';
							statusMessageElem.style.opacity = '0.9';
						}, 1000);
					}, 2000);
				}

				setTimeout(() => {
					window.location.reload();
				}, 2100);
			}
		}
		var formData = new FormData(); 
		formData.append('MM_insert', 'remFromList');
		formData.append('songId', songId);
	
		xmlhttp.open("POST", "logic/discover_be.php", true);
		xmlhttp.send(formData);
	});


	let userId = currentUser.user_id;
    let menuBtns = document.querySelectorAll('.song-actions');
    menuBtns.forEach((items) => {
        let user = items.getAttribute('data-owner');
        if (user === userId) {
            // items.style.display = '';
            document.querySelectorAll(".deleteFile, .otherOption").forEach(function(obtion) {
                obtion.style.display = '';
            });
        }
    });

});

function closeMiniMenu() {
    let bgOverlayer = document.querySelector('.bg-overlayer');
    let menuList = document.querySelectorAll('.song-actions');
        
    menuList.forEach((element)=>{
        if(element.style.display === 'block') {
            element.style.display = 'none';
            bgOverlayer.style.display = 'none';
        }
    });
}

function login() {
	let bg_popup = document.getElementById('bg_popup');
    bg_popup.style.display = 'block';

    var displaySize = {
        "width": "450px",
        "height": "458px",
        "margin": "5vh auto"
    };
     
    var bgContainer = document.getElementById("bg_container");
    Object.assign(bgContainer.style, displaySize);

    let formular_front = document.getElementById('formular_front');
    formular_front.style.display = 'block';
}

function add_to_list(song) {
    let bg_popup = document.getElementById('bg_popup');
    bg_popup.style.display = 'block';

    var displaySize = {
        "width": "350px",
        "height": "458px",
        "margin": "5vh auto"
    };
     
    var bgContainer = document.getElementById("bg_container");
    Object.assign(bgContainer.style, displaySize);

    let formular_songs_list = document.getElementById('formular_songs_list');
    formular_songs_list.setAttribute('data-songId', song);
    formular_songs_list.style.display = 'block';

    closeMiniMenu()
}

function confirm_remove(song) {
	let bg_popup = document.getElementById('bg_popup');
    bg_popup.style.display = 'block';

	var displaySize = {
        "width": "350px",
        "height": "150px",
        "margin": "25vh auto"
    };
     
    var bgContainer = document.getElementById("bg_container");
    Object.assign(bgContainer.style, displaySize);

	let formular_remove_song = document.getElementById('formular_remove_song');
    formular_remove_song.setAttribute('data-removeId', song);
    formular_remove_song.style.display = 'block';

	closeMiniMenu()
}

//Close popups
document.addEventListener('mouseup', function(e) {
    var bg_container = document.getElementById('bg_container');

    if (!bg_container.contains(e.target)) {
        close_popup();
    }
});

let cancelBtn = document.getElementById('cancel-btn');
cancelBtn.addEventListener('click', function(){
	close_popup()
});

function close_popup() {
    var bg_popup = document.getElementById('bg_popup');
    bg_popup.style.display = 'none';

    var formular_songs_list = document.getElementById('formular_songs_list');
    formular_songs_list.style.display = 'none';

    var formular_front = document.getElementById('formular_front');
    formular_front.style.display = 'none';

    var formular_songs_list = document.getElementById('formular_songs_list');
    formular_songs_list.style.display = 'none';
}

let listName = document.querySelectorAll('.list');
listName.forEach((element)=>{
    element.addEventListener('click', getListId);
});

function getListId(event) {
    var list = event.target.closest('.list').getAttribute('data-list');
    console.log(list);

    var urlActual = window.location.href;
    urlActual = urlActual.replace(/#(?=\?)/, "");
    var newUrl = urlActual.replace(/(\?|\&)list=[^&]+/, "");

    window.location.href = newUrl + (newUrl.includes("?") ? "&" : "?") + "list=" + list;

};

let menuBtn = document.querySelectorAll('.actions-btn');
let menuList = document.querySelectorAll('.song-actions');

menuBtn.forEach((element)=>{
    element.addEventListener('click', pullDownMenu);
});

function pullDownMenu(event) {
    let menuList = event.currentTarget.nextElementSibling;
    let bgOverlayer = document.querySelector('.bg-overlayer');

    if (menuList.style.display === 'block') {
        menuList.style.display = 'none';
        bgOverlayer.style.display = 'none';
    } else {
        let otherMenuList = document.querySelectorAll('.song-actions');

        otherMenuList.forEach((element)=>{
            element.style.display = 'none';
        });

        menuList.style.display = 'block';
        bgOverlayer.style.display = 'block';
    }
};