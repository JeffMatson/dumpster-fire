/*gruntfile.js*/

module.exports = function(grunt) {

    grunt.initConfig({
        pkg: grunt.file.readJSON('package.json'),

        browserify: {
            dist: {
                options: {
                    transform: [['babelify', {presets: ['es2015', 'react']}]]
                },
                src: ['js/src/add-new.jsx'],
                dest: 'js/build/app.js'
            }
        }

    });

    grunt.loadNpmTasks('grunt-browserify');

    grunt.registerTask('default', ['browserify']);

};