<template>
    <textarea>{{ content }}</textarea>
</template>

<script>
	import App     from './App.vue';
	import TinyMCE from 'tinymce/tinymce';
	import              'tinymce/themes/modern';

    export default{
        props: ['content', 'selectedPost'],
        data(){
            return{}
        },
        methods: {
            sendEmit( contents ) {
                this.$emit('textChanged', contents );
            },
        },
        mounted() {
            var self = this;

                return TinyMCE.init({
                    skin: false,
                    selector: '#' + self.$el.id,
                    height: 500,
                    setup: function (editor) {
                        editor.on('change', function(e) {
                            self.sendEmit( editor.getContent() );
                        });

                    },
                    plugins: [],
                    toolbar: 'undo redo | insert | styleselect | bold italic | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link',
                });
        },
        watch: {
            selectedPost( value ) {
                var editor = TinyMCE.get( this.$el.id );
                editor.setContent( this.content );
            }
        }
    }
</script>
