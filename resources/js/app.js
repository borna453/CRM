import 'flowbite';
import './bootstrap';
import confetti from 'canvas-confetti';
import './datepicker';
import 'basiclightbox/dist/basicLightbox.min.css';
import * as basicLightbox from 'basiclightbox';
import intersect from '@alpinejs/intersect';
window.Alpine.plugin(intersect)
import 'trix';

window.basicLightbox = basicLightbox;

import.meta.glob([
    '../images/**',
]);

window.addEventListener('confetti', () => {
    confetti({
        particleCount: 100,
        spread: 70,
        origin: { y: 0.6 }
     });
});

document.querySelectorAll('input[datepicker]').forEach(function (datepickerInput) {
    datepickerInput.addEventListener('changeDate', function () {
        const event = new Event('input');
        datepickerInput.dispatchEvent(event);
    });
});

// Format: d-m-Y H:i:s
window.dateTimeHandler = {
    extractMonth: function (date) {
        return parseInt(date.split('-')[1]);
    },
    extractYear: function (date) {
        return parseInt(date.split('-')[2]);
    },
    extractDay: function (date) {
        return parseInt(date.split('-')[0]);
    },
    extractHour: function (date) {
        return parseInt(date.split(' ')[1].split(':')[0]);
    },
    extractMinute: function (date) {
        return parseInt(date.split(' ')[1].split(':')[1]);
    },
    extractSecond: function (date) {
        return parseInt(date.split(' ')[1].split(':')[2]);
    },
};

document.addEventListener('DOMContentLoaded', function () {
    document.addEventListener('keydown', function(event){
        if(event.key === 'Escape' && window.currentBasicLightboxInstance){
            window.currentBasicLightboxInstance.close();
            window.currentBasicLightboxInstance = null;
        }
    });
});

document.addEventListener('click', function (event) {
    // Check if the clicked element has a data-lightbox attribute
    const lightboxTrigger = event.target.closest('a[data-lightbox]');

    if (lightboxTrigger) {
        event.preventDefault(); // Prevent default link behavior
        const imageUrl = lightboxTrigger.getAttribute('data-lightbox');

        // Create and show the lightbox
        const instance = window.basicLightbox.create(`
            <img src="${imageUrl}" class="w-full h-auto">
        `);
        instance.show();
        window.currentBasicLightboxInstance = instance;
    }
});


// jstimezonedetect
import jstz from 'jstimezonedetect';

window.determineTimezone = () => jstz.determine().name();
