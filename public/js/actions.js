document.addEventListener("DOMContentLoaded", () => {
    let searchField = document.getElementById('search-field');
    searchField.addEventListener('keyup', search);

    function search(event) {
        var searching = event.target.value;
    
        var xmlhttp = new XMLHttpRequest();
        xmlhttp.onreadystatechange = function() {
            if (this.readyState == 4 && this.status == 200) {
                var response = this.responseText;
                response = JSON.parse(response);

                console.log(response);

                if (Array.isArray(response)) {
                    // Generar la tabla HTML con los resultados
                    var table = document.createElement('table');
                    table.className = 'music-list';
        
                    response.forEach(function(song) {
                        var row = document.createElement('tr');

                        var songCoverCell = document.createElement('td');
                            var songCoverDiv = document.createElement('div');
                            songCoverDiv.className = 'songs-cover';

                            var songCoverImg = document.createElement('img');
                            songCoverImg.src = 'images/profile/' + song.cover + 'perfil.png';
                            songCoverImg.alt = '';

                            songCoverDiv.appendChild(songCoverImg);
                            songCoverCell.appendChild(songCoverDiv);
        

                        var artistCell = document.createElement('td');
                        artistCell.textContent = song.artist+' - '+song.songName;
        
                        var songNameCell = document.createElement('td');
                            songNameCell.setAttribute('width', '5%');
                            
                            var actionsBtn = document.createElement('button');
                            actionsBtn.className = 'actions-btn';
                            actionsBtn.setAttribute('data-menu', song.songId);
                            actionsBtn.textContent = 'o o o';
                            songNameCell.appendChild(actionsBtn);

                            var songActionsDiv = document.createElement('div');
                            songActionsDiv.className = 'song-actions';
                            songActionsDiv.setAttribute('id', 'song-actions');

                            var ul = document.createElement('ul');
                            var li1 = document.createElement('li');
                            li1.textContent = 'Action 1';
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
        
                    // Agregar la tabla al elemento contenedor deseado
                    var resultContainer = document.getElementById('list-content');
                    resultContainer.innerHTML = '';
                    resultContainer.appendChild(table);

                    let menuBtn = document.querySelectorAll('.actions-btn');
                    // let menuList = document.querySelectorAll('.song-actions');

                    menuBtn.forEach((element)=>{
                        element.addEventListener('click', pullDownMenu);
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
});

function login() {
    console.log('aqui');

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

//Close popups
document.addEventListener('mouseup', function(e) {
    var bg_container = document.getElementById('bg_container');

    if (!bg_container.contains(e.target)) {
        close_popup();
    }
});

function close_popup() {
    var bg_popup = document.getElementById('bg_popup');
    bg_popup.style.display = 'none';

    var formular_front = document.getElementById('formular_front');
    formular_front.style.display = 'none';

    // var comment_fotos_popup = document.getElementById('comment_fotos_popup');
    // comment_fotos_popup.style.display = 'none';

    // var post_form = document.getElementById('post_form');
    // post_form.style.display = 'none';

    // var pending_menu = document.getElementById('pending_menu');
    // pending_menu.style.display = 'none';

    // var activity_list = document.getElementById('activity_list');
    // activity_list.style.display = 'none';

    // var followers_list = document.getElementById('followers_list');
    // followers_list.style.display = 'none';

    // var list_i_follow = document.getElementById('list_i_follow');
    // list_i_follow.style.display = 'none';

    // uploadFilesModule.classList.remove('show_modal');
    // cameraModule.classList.remove('show_modal');
}

// function close_popup() {
//     var elementsToClose = [
//         document.getElementById('bg_popup'),
//         document.getElementById('formular_front'),
//         // Agrega aquí los demás elementos que deseas cerrar
//         // document.getElementById('comment_fotos_popup'),
//         // document.getElementById('post_form'),
//         // ...
//     ];

//     var clickedInsideElement = false;

//     document.addEventListener('click', function(event) {
//         for (var i = 0; i < elementsToClose.length; i++) {
//             if (elementsToClose[i].contains(event.target)) {
//                 clickedInsideElement = true;
//                 break;
//             }
//         }

//         if (!clickedInsideElement) {
//             for (var i = 0; i < elementsToClose.length; i++) {
//                 elementsToClose[i].style.display = 'none';
//             }
//         }

//         clickedInsideElement = false;
//     });
// }


let listName = document.querySelectorAll('.list-name');

listName.forEach((element)=>{
    element.addEventListener('click', getListId);
});

function getListId(event) {
    var list = event.target.getAttribute('data-list');
    console.log(list);

    var urlActual = window.location.href;
    var newUrl = urlActual.replace(/(\?|\&)list=[^&]+/, ""); // Elimina el parámetro existente ?list=...

    window.location.href = newUrl + (newUrl.includes("?") ? "&" : "?") + "list=" + list;

};


let menuBtn = document.querySelectorAll('.actions-btn');
let menuList = document.querySelectorAll('.song-actions');

menuBtn.forEach((element)=>{
    element.addEventListener('click', pullDownMenu);
});

function pullDownMenu(event) {
    let menuList = event.currentTarget.nextElementSibling;
    
    if (menuList.style.display === 'block') {
        menuList.style.display = 'none';
    } else {
        let otherMenuList = document.querySelectorAll('.song-actions');

        otherMenuList.forEach((element)=>{
            element.style.display = 'none';
        });

        menuList.style.display = 'block';
    }
};