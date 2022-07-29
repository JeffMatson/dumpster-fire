const express = require('express');
const app = express();
var WPAPI = require('wpapi');
var handlebars = require('handlebars');
var pluginHeaders = require('./templates/pluginHeaders');
var mkdirp = require('mkdirp');
var fs = require('fs');
var path = require('path');
var zip = new require('node-zip');
const bodyParser = require('body-parser');
var stripTags = require('striptags');
var Config = require('config');
const config = new Config();

app.use(bodyParser.json());

if (!fs.existsSync('./tmp')) {
    fs.mkdirSync('./tmp');
}

function hasValidApiKey(request) {
    console.log('API key received.');
    var isValid = config.api_keys.indexOf(request.query.key) !== -1;
    console.log('Key status: ' + isValid);
    return isValid;
}

function isForced(request) {
    console.log(typeof request.query.force);
    return request.query.force === '1';
}

var wp = new WPAPI({
    endpoint: 'http://' + config.siteDomain + '/wp-json'
});

wp.snippetsEndpoint = wp.registerRoute('wp/v2', 'snippets/(?P<id>)');

function generateZip(slug, files) {
    var zipFile = new zip();

    files.forEach(function (file) {
        zipFile.file(slug + '/' + file.name, file.contents);
    });

    return zipFile.generate({
        base64: false,
        compression: 'DEFLATE'
    });
}

function existsInCache(fileName) {
    return fs.existsSync(path.join(__dirname, 'tmp', fileName));
}

function checkCacheAge(fileName, isForced = false) {

    if (isForced) {
        return false;
    }
    if (existsInCache(fileName)) {
        var details = fs.statSync(path.join(__dirname, 'tmp', fileName));
        return new Date(details.birthtime);
    } else {
        return false;
    }
}


