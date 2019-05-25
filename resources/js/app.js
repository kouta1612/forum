require("./bootstrap");

Vue.component("flash", require("./components/Flash.vue").default);
Vue.component('paginator', require('./components/Paginator.vue').default);
Vue.component('user-notifications', require('./components/UserNotifications').default);
Vue.component('avatar-form', require('./components/AvatarForm.vue').default);

Vue.component("threadView", require("./pages/Thread.vue").default);

const app = new Vue({
    el: "#app"
});
