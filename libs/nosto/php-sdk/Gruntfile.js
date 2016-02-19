module.exports = function(grunt) {
    // Project configuration.
    grunt.initConfig({
      uglify: {
        my_target: {
          files: {
            'js/NostoIframe.min.js': ['js/src/NostoIframe.js']
          }
        }
      }
    });

    // Load the plugin that provides the "uglify" task.
    grunt.loadNpmTasks('grunt-contrib-uglify');

    // Default task(s).
    grunt.registerTask('default', ['uglify']);
};