import requestHeaders from "../framework/RequestHeaders";

document.addEventListener('DOMContentLoaded', function () {
    const photoGrid = document.querySelector('#photo-grid');
    const photoView = document.querySelector('#photo-view');
    const closeBtn = document.querySelector('.close-btn');

    photoGrid.addEventListener('click', (e) => {
        if (e.target.closest('div.grid-item') === null) {
            return;
        }
        const img = e.target.closest('div.grid-item').querySelector('img');
        const uuid = img.dataset.uuid;

        fetch(`/photo/${uuid}`, {
            headers: requestHeaders.html()
        })
            .then(response => response.text())
        .then(data => {
            if (data.toLowerCase().startsWith('<!doctype')) {
                $('html').innerHTML = data;
            } else {
                photoView.querySelector('.photo-view-content').innerHTML = data;
                const parentWidth = photoView.parentElement.clientWidth;
                photoView.style.width = parentWidth + 'px';
            }
        });

        photoView.classList.add('active');
        photoGrid.classList.add('single-column');
        document.body.style.overflow = 'hidden';
    });

    closeBtn.addEventListener('click', () => {
        photoView.classList.remove('active');
        photoGrid.classList.remove('single-column');
        document.body.style.overflow = '';
    });

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && photoView.classList.contains('active')) {
            closeBtn.click();
        }
    });
});
