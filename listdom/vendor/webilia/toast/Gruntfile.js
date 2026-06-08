"use strict";

const sass = require("sass");

module.exports = function (grunt) {
    grunt.initConfig({
        watch: {
            sass: {
                files: ["assets/scss/**/*.scss"],
                tasks: ["sass:dev", "cssmin"],
            },
            js: {
                files: ["assets/js/webilia-toast.js"],
                tasks: ["uglify"],
            },
        },

        uglify: {
            options: {},
            target: {
                files: {
                    "assets/js/webilia-toast.min.js": ["assets/js/webilia-toast.js"],
                },
            },
        },

        sass: {
            options: {
                style: "expanded",
                sourceMap: false,
                implementation: sass,
            },
            dev: {
                files: {
                    "assets/css/webilia-toast.css": "assets/scss/webilia-toast.scss",
                },
            },
            prod: {
                files: {
                    "assets/css/webilia-toast.css": "assets/scss/webilia-toast.scss",
                },
            },
        },

        cssmin: {
            options: {
                sourceMap: false,
            },
            target: {
                files: {
                    "assets/css/webilia-toast.min.css": ["assets/css/webilia-toast.css"],
                },
            },
        },
    });

    grunt.loadNpmTasks("grunt-contrib-watch");
    grunt.loadNpmTasks("grunt-contrib-uglify");
    grunt.loadNpmTasks("grunt-contrib-sass");
    grunt.loadNpmTasks("grunt-contrib-cssmin");

    grunt.registerTask("default", ["uglify", "sass:prod", "cssmin"]);
};
