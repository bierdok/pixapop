import '../css/app.styl'

import {sprintf} from 'sprintf-js'
import FastClick from 'fastclick'
import filesize from 'filesize'
import Fullscreen from 'vue-fullscreen/src/component.vue'
import ImagePreloader from 'image-preloader'
import moment from 'moment'
import Vue from 'vue'
import Vue2Touch from 'vue2-touch'
import VueLazyload from 'vue-lazyload'
import VueMaterialIcon from 'vue-material-icon'
import VueProgressiveImage from 'vue-progressive-image'

const preloader = new ImagePreloader();
Vue.component(VueMaterialIcon.name, VueMaterialIcon);

new Vue({
    el: '#app',
    components: {
        Fullscreen
    },
    data: {
        fullscreen: false,
        pixapop: window.pixapop,
        nav: null,
        preview: {
            photo: null,
            width: 0,
            height: 0,
            index: 0
        },
        gallery: []
    },
    mounted() {
        FastClick.attach(document.body);
        Vue.use(Vue2Touch, {
            gestures: ['swipe'],
            directions: {
                swipe: ['swipeleft', 'swiperight']
            }
        });
        Vue.use(VueLazyload, {
            preLoad: 2,
        });
        Vue.use(VueProgressiveImage);
        moment.locale(window.locale);
        this.loadGallery(null);
        this.$nextTick(function() {
            window.addEventListener('resize', this.refreshViewport)
        })
    },
    methods: {
        onToggleFullscreen(fullscreen) {
            this.fullscreen = fullscreen
        },
        toggleFullscreen() {
            this.$refs['fullscreen'].toggle()
        },
        getFullscreenIcon() {
            return 'fullscreen' + (this.fullscreen ? '_exit' : '')
        },
        getGalleryLabel(month, count) {
            return sprintf('%s (%d)', moment(month).format('MMMM YYYY'), count)
        },
        loadGallery(event) {
            this.gallery = this.pixapop[(event && event.target.value) || Object.keys(this.pixapop)[0]];
            this.closePreview();
            window.scrollTo(0, 0)
        },
        openPreview(index) {
            if (index < 0 || index > this.gallery.length - 1) {
                return
            }
            this.refreshViewport();
            this.preview.index = index;
            this.preview.photo = this.gallery[this.preview.index];
            if (this.preview.index < this.gallery.length - 1) {
                preloader.preload(this.getPreviewPath(this.gallery[this.preview.index + 1]))
            }
        },
        onPreviewSwipe(type) {
            if (type === 'swiperight') {
                this.openPreview(this.preview.index - 1)
            } else if (type === 'swipeleft') {
                this.openPreview(this.preview.index + 1)
            }
        },
        closePreview() {
            this.preview.photo = null
        },
        getPhotoSize(photo) {
            return filesize(photo.size, {locale: window.locale})
        },
        getPreviewPath(photo) {
            return sprintf('media/cache/generate/preview/%dx%d/%s', this.preview.width, this.preview.height, photo.name)
        },
        getThumbnailPath(photo) {
            return sprintf('media/cache/resolve/thumbnail/%s', photo.name)
        },
        refreshViewport() {
            this.preview.width = document.documentElement.clientWidth;
            this.preview.height = document.documentElement.clientHeight
        }
    }
});
