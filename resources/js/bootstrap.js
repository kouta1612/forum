window.Vue = require("vue");

Vue.prototype.$authorize = function (handler) {
    let user = window.App.user;

    return user ? handler(user) : false;
};

try {
    window.Popper = require('popper.js').default;
    window.$ = window.jQuery = require('jquery');

    require('bootstrap');
} catch (e) { }

window.axios = require("axios");
window.axios.defaults.headers.common["X-Requested-With"] = "XMLHttpRequest";

let token = document.head.querySelector('meta[name="csrf-token"]');

if (token) {
    window.axios.defaults.headers.common["X-CSRF-TOKEN"] = token.content;
} else {
    console.error(
        "CSRF token not found: https://laravel.com/docs/csrf#csrf-x-csrf-token"
    );
}

window.events = new Vue();

window.flash = function (message, level = 'success') {
    window.events.$emit("flash", { message, level });
};
