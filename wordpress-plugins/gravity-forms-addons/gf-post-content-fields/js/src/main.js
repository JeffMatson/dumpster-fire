import Vue from 'vue';
import App from './App.vue';

export const wp = wp;

if (window.gfpcf !== undefined && window.gfpcf !== null) {

    window.gfpcf.forEach(function(inputItem) {

        new Vue({
            el: '#gf-post-content-editor-' + inputItem.id,
            render: h => h(App),
            data: function() {
                return {
                    boundInputId: inputItem.id,
                    posts: inputItem.posts,
                    existing: inputItem.existing
                };
            }
        });

    });

}