import '../framework/dom-selectors.js';
import requestHeaders from "../framework/RequestHeaders.js";
import {Notify} from "../framework/Notify.js";

$('#photo-upload-submit').addEventListener('click', function(e) {
    e.preventDefault();
    const form = this.form;
    const url = new URL(form.action);
    const formData = new FormData(form);
    for (let i = 0; i < form.files.length; i++) {
        formData.append('files', form.files[i]);
    }
    fetch(url, {
        method: form.method,
        headers: requestHeaders.json(),
        credentials: 'include',
        body: formData
    })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                new Notify('success', data.message);
                form.reset();
            } else {
                new Notify('error', data.message);
            }
        });
    });

$('#select-directory-submit').addEventListener('click', function(e) {
    e.preventDefault();
    const form = this.form;

    if (!form.reportValidity()) {
        return;
    }

    const url = new URL(form.action);
    const formData = new FormData(form);
    fetch(url, {
        method: form.method,
        headers: requestHeaders.json(),
        credentials: 'include',
        body: formData
    })
        .then(response => response.json())
        .then(data => {

        });
});

$('#upload-flickr-data-btn').addEventListener('click', function(e) {
    e.preventDefault();
    const url = this.form.url;
    const method = this.form.method;
    fetch(url, {
        method,
        headers: requestHeaders.json(),
        credentials: 'include'
    })
        .then(response => response.json())
        .then(data => {

        });
});