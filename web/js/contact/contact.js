import RequestHeaders from "../framework/RequestHeaders.js";
import {Notify} from "../framework/Notify.js";

let theEditor;

ClassicEditor
    .create(document.querySelector('#message'))
    .then(editor => {
        theEditor = editor; // Save for later use.
    });

document.querySelector('#send-email-btn').addEventListener('click', sendEmailMessage);

function sendEmailMessage(event)
{
    event.preventDefault();
    const form = document.querySelector('#send-email-form');
    theEditor.updateSourceElement();
    if (form.reportValidity()) {
        let formData = new FormData(form);
        const headers = RequestHeaders.jsonWithToken();
        headers.append('Content-Type', 'application/x-www-form-urlencoded');
        fetch('/contact/send', {
            method: 'POST',
            headers: headers,
            body: JSON.stringify(Object.fromEntries(formData.entries())),
        }).then(response => response.json())
            .then(data => {
                document.querySelector('#contact-selected-icon-name').innerText = data.captcha.selectedIcon.name;
                document.querySelector('#contact-captcha2').value = data.captcha.encryptedIcon;
                if (data.success) {
                    new Notify('success', 'Success! Message Sent.');
                    form.reset();
                } else {
                    new Notify('error', 'Sorry, there was an error');
                }
            });
    } else {
        new Notify('error', 'Sorry, there is a problem with the form');
    }
}
