document.addEventListener("DOMContentLoaded", () => {

    
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

// function createpost() {
//     var picNames = document.querySelector('.post_foto_prev').getAttribute('data-pic-names');
//     var postContent = document.getElementById('post_content').value;
    
//     var xmlhttp = new XMLHttpRequest();
//     xmlhttp.onreadystatechange = function() {
//         if (this.readyState == 4 && this.status == 200) {
//             document.getElementById('content').innerHTML = this.responseText;
//             initButtons();
//             initSlides();
            
//             let bg_popup = document.getElementById('bg_popup');
//             bg_popup.style.display = 'none';

//             updatePostCount();
//         }
//     };
//     var formData = new FormData(); 
//     formData.append('MM_insert', 'formnewpost');
//     formData.append('pic_name', picNames);
//     formData.append('post_content', postContent);

//     xmlhttp.open("POST", "logic/start_be.php", true);
//     xmlhttp.send(formData);
// }

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