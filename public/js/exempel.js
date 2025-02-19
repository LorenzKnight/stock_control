// 2 scripts para refrescar despues de un clic o accion
document.addEventListener('click', function(e) {
    console.log(e);
    if (!e.target.classList.contains('album-dislike')) { 
        return
    }
    let element = e.target 
    let albumId = element.getAttribute('data-albumId');

    var xmlhttp = new XMLHttpRequest();
    xmlhttp.onreadystatechange = function() {
        if (this.readyState == 4 && this.status == 200) {
            var response = JSON.parse(this.responseText);
            console.log(response);

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

            if (response.success && response.html) {
                document.getElementById('list-content').innerHTML = response.html;
                // dislikeBottonHander();
            }
        }
    }
    var formData = new FormData(); 
    formData.append('MM_insert', 'removeFromFav');
    formData.append('albumId', albumId);

    xmlhttp.open("POST", "logic/discover_be.php", true);
    xmlhttp.send(formData);
});

// script 2
function dislikeBottonHander() {
    let albumDislike = document.querySelectorAll('.album-dislike');

    albumDislike.forEach(function(element) {
        element.addEventListener('click', function() {
            let albumId = element.getAttribute('data-albumId');

            var xmlhttp = new XMLHttpRequest();
            xmlhttp.onreadystatechange = function() {
                if (this.readyState == 4 && this.status == 200) {
                    var response = JSON.parse(this.responseText);
                    console.log(response);

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

                    if (response.success && response.html) {
                        document.getElementById('list-content').innerHTML = response.html;
                        dislikeBottonHander();
                    }
                }
            }
            var formData = new FormData(); 
            formData.append('MM_insert', 'removeFromFav');
            formData.append('albumId', albumId);

            xmlhttp.open("POST", "logic/discover_be.php", true);
            xmlhttp.send(formData);
        });
    });
}