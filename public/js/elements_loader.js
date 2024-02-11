
document.addEventListener("DOMContentLoaded", () => {
    songs(); // ESTA PARTE ES PARA DESARROLLA O MOSTRAR LO QUE PHP ESTA MANDANDO 
});

function songs() {
    fetch("logic/songs.php")
	.then(res => res.json())
	.then(data => {
		const mainContent = document.querySelector('#main-content2');
		mainContent.innerHTML = '';

        data.forEach((list) => {
            const listDiv = document.createElement('div');
            listDiv.className = 'list';
			listDiv.setAttribute('data-list', list.lid);

			const listCoverDiv = document.createElement('div');
			listCoverDiv.className = 'list-cover';

			const listInfoDiv = document.createElement('div');
			listInfoDiv.className = 'list-info';

            const listNameDiv = document.createElement('div');
            listNameDiv.className = 'list-name';
            listNameDiv.textContent = list.listName;

            const listUserP = document.createElement('p');
            listUserP.className = 'list-user-p';
            listUserP.textContent = list.userData.name+' '+list.userData.surname;

            mainContent.appendChild(listDiv);
			listDiv.appendChild(listCoverDiv);
			listDiv.appendChild(listInfoDiv);
			listInfoDiv.appendChild(listNameDiv);
            listInfoDiv.appendChild(listUserP);
        });
    })
    .catch(error => console.error('Error fetching data:', error));	
}