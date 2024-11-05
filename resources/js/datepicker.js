import Datepicker from 'flowbite-datepicker/Datepicker';
import DateRangePicker from 'flowbite-datepicker/DateRangePicker';
import DatePickerLocaleNl from 'flowbite-datepicker/locales/nl';

Object.assign(Datepicker.locales, DatePickerLocaleNl);

const getDatepickerOptions = (datepickerEl) => {
    const buttons = datepickerEl.hasAttribute('datepicker-buttons');
    const autohide = datepickerEl.hasAttribute('datepicker-autohide');
    const format = datepickerEl.hasAttribute('datepicker-format');
    const orientation = datepickerEl.hasAttribute('datepicker-orientation');
    const title = datepickerEl.hasAttribute('datepicker-title');
    const language = datepickerEl.hasAttribute('datepicker-language');
    const init = datepickerEl.hasAttribute('datepicker-init');

    let options = {};
    if (buttons) {
        options.todayBtn = true;
        options.clearBtn = true;
    }
    if (autohide) options.autohide = true;
    if (format) options.format = datepickerEl.getAttribute('datepicker-format');
    if (orientation) options.orientation = datepickerEl.getAttribute('datepicker-orientation');
    if (title) options.title = datepickerEl.getAttribute('datepicker-title');
    if (language) options.language = datepickerEl.getAttribute('datepicker-language');
    if (init) options.init = datepickerEl.getAttribute('datepicker-init');

    return options;
};

window.initDatePicker = function (datepickerEl) {
    const { init, ...options } = getDatepickerOptions(datepickerEl);

    new Datepicker(datepickerEl, options);

    const callback = () => {
        if (init && window[init] && typeof window[init] === 'function') {
            window[init]();
        }
    };

    callback();

    datepickerEl.addEventListener('changeMonth', () => setTimeout(callback, 100));
    datepickerEl.addEventListener('changeYear', () => setTimeout(callback, 100));
};

document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('[datepicker]').forEach(function (datepickerEl) {
        new Datepicker(datepickerEl, getDatepickerOptions(datepickerEl));
    });

    document.querySelectorAll('[inline-datepicker]').forEach(initDatePicker);

    document.querySelectorAll('[date-rangepicker]').forEach(function (datepickerEl) {
        new DateRangePicker(datepickerEl, getDatepickerOptions(datepickerEl));
    });
});
