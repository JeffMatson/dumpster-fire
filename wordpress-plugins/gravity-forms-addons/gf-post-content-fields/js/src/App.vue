<template>
	<div>
		<div v-for="(item, key, index) in editorItems" :key="item.id">

			<select v-model="item.selectedPost" @change="item.content = item.selectedPost.post_content">
				<option v-for="post in postSelections" :value="post">{{ post.post_title }}</option>
			</select>

			<button class="upload-image" @click="addMedia($event, item.id)">Upload Image</button>

			<post-editor v-bind:content="item.content" :id="item.id" v-bind:editingitem="item" @textChanged="item.content = $event" :selectedPost="item.selectedPost.post_content"></post-editor>

			<div class="delete-item" @click="deleteItem( item.id )">Delete Item</div>
			<div class="add-item" @click="addNew()">Add Item</div>

		</div>
		<textarea :name="'input_' + input_id" v-model="editorItems" style="display:none;"></textarea>

	</div>
</template>

<script>
import PostEditor from './PostEditor.vue';
import TinyMCE from 'tinymce/tinymce';

export default {
  name: 'text-editor',
  data () {
	return {
	  editorItems: [
	    {
	        id: Math.random().toString(36).substr(2),
	        selectedPost: {
	            post_content: ''
	        },
	        content: '',
	    }
	  ],
	  postSelections: this.$parent.posts,
	  input_id: this.$parent.boundInputId
	}
  },
  components: {
    'post-editor': PostEditor
  },
  mounted() {
	  var existingData = this.$parent.existing;
	  if ( existingData.length !== 0 && existingData !== '{}' ) {
		  this.editorItems = existingData;
	  }

  },
  methods: {
    addNew() {
        this.editorItems.push( {
            'id': Math.random().toString(36).substr(2),
            'content': '',
            selectedPost: {
	            post_content: ''
	        }
	    } );
    },
    addMedia( event, id ) {
        event.preventDefault();

		this.$nextTick(function () {

			jQuery( function() {
				window.frame = wp.media({
	                title: 'Insert Media',
	                button: {
	                    text: 'Use this media'
	                },
	                multiple: true
	            });

	            window.frame.on( 'select', function() {
		            var attachment = window.frame.state().get( 'selection' ).first().toJSON();
		            var editor     = TinyMCE.get( id );

		            editor.execCommand( 'mceInsertContent', false, '<img src="' + attachment.url + '">' );
	            });

	            window.frame.open();

			});
		});
    },
    deleteItem( key ) {
        var editorItems = this.editorItems;
        var newArray    = editorItems.filter( function( value ) {
            return value.id !== key;
        });

        this.editorItems = newArray;
    }
  }
}

</script>
