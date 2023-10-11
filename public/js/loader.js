function loadScript(url) {
    return new Promise((resolve, reject) => {
        const script = document.createElement('script');
        script.src = url;
        script.onload = resolve;   // El script ha sido cargado y ejecutado correctamente.
        script.onerror = reject;   // Hubo un error al cargar el script.
        document.body.appendChild(script);
    });
}

loadScript("js/music_player.js")
    .then(() => {
        return loadScript("js/actions.js");
    })
    .then(() => {
    return loadScript("js/uploader.js");
    })
    // .then(() => {
    //     return loadScript("js/extra_functions.js");
    // })
    .catch(error => {
        console.error("Error cargando el script:", error);
    });