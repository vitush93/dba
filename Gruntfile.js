module.exports = function (grunt) {

    require('time-grunt')(grunt);
    grunt.initConfig({
        pkg: grunt.file.readJSON('package.json'),

        sass: {
            options: {
                includePaths: [
                    'bower_components/bootstrap-sass/assets/stylesheets'
                ]
            },
            dev: {
                options: {
                    sourceMap: true
                },
                files: {
                    'www/css/master.css': 'assets/scss/main.scss'
                }
            },
            dist: {
                options: {
                    outputStyle: 'compressed'
                },
                files: {
                    'www/css/master.css': 'assets/scss/main.scss'
                }
            }
        },

        autoprefixer: {
            dist: {
                files: {
                    'www/css/master.css': 'www/css/master.css'
                }
            }
        },

        uglify: {
            dist: {
                files: {
                    'www/js/master.js': 'www/js/master.js'
                }
            }
        },

        browserify: {
            dist: {
                files: {
                    './www/js/master.js': ['./assets/js/main.js']
                }
            }
        },

        watch: {
            grunt: {
                options: {
                    reload: true
                },
                files: ['Gruntfile.js']
            },
            js: {
                files: ['assets/js/**/*.js'],
                tasks: ['browserify']
            },
            sass: {
                files: ['assets/scss/**/*.scss'],
                tasks: ['sass:dev']
            }
        }
    });

    grunt.loadNpmTasks('grunt-browserify');
    grunt.loadNpmTasks('grunt-sass');
    grunt.loadNpmTasks('grunt-contrib-watch');
    grunt.loadNpmTasks('grunt-contrib-uglify');
    grunt.loadNpmTasks('grunt-autoprefixer');

    grunt.registerTask('default', ['build', 'watch']);
    grunt.registerTask('build', ['sass:dev', 'browserify']);

    grunt.registerTask('release', ['sass:dist', 'autoprefixer', 'browserify', 'uglify']);
};