const app = module.exports = require('express')();

app.get('/:userId/:snippetId', function (request, resolve) {
	var snippetId = request.params.snippetId;
	wp.snippetsEndpoint().id(snippetId).get(function (err, data) {
		if (err) {
			console.log(err);
			resolve.send('Looks like something broke.');
		}

		var postLastModified = new Date(data.modified);

		var pluginDirPath = path.join(__dirname, 'tmp', data.slug);
		var pluginFilePath = path.join(pluginDirPath, data.slug + '.php');

		var cacheAge = checkCacheAge(data.slug + '.zip', isForced(request));

		// console.log('Force: ' + request.query.force === 1);
		console.log('Valid: ' + hasValidApiKey(request));

		console.log('Cache age: ' + cacheAge);

		if ((!cacheAge) || (cacheAge <= postLastModified)) {

			console.log('Generating New Plugin Zip');
			var snippetContents = data.snippet_content;
			var pluginFileContents = handlebars.compile(pluginHeaders.template + '\n' + snippetContents);
			console.log(data);
			var pluginVars = {
				'plugin_name_prefix': 'WordBits',
				'plugin_name': data.slug,
				'plugin_uri': data.link,
				'plugin_description': stripTags(data.excerpt.rendered),
				'plugin_version': '1.0',
				'plugin_author': data.snippet_author + ', WordBits',
				'plugin_author_uri': data.snippet_author_url,
				'plugin_text_domain': 'wordbits'
			};
			var pluginFileCompiled = pluginFileContents(pluginVars);

			var generatedZip = generateZip(data.slug, [{
				name: data.slug + '.php',
				contents: pluginFileCompiled
			}]);
			fs.writeFileSync(path.join(__dirname, 'tmp', data.slug + '.zip'), generatedZip, 'binary');
		}

		resolve.download(path.join(__dirname, 'tmp', data.slug + '.zip'));

	});
});
