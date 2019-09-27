Nova.booting((Vue, router) => {
    Vue.component('index-NovaGalleryField', require('./components/IndexField'));
    Vue.component('detail-NovaGalleryField', require('./components/DetailField'));
    Vue.component('form-NovaGalleryField', require('./components/FormField'));
});
