const app = module.exports = require('express')();

app.post('/:slug', function (request, resolve) {
	if (config.api_keys.indexOf(request.body.key) === -1) {
		resolve.send('You broke something.', 404);
		return;
	}

	fs.stat(path.join(__dirname, 'tmp', request.params.slug + '.zip'), function (err, stats) {
		if (!err) {
			fs.unlinkSync(path.join(__dirname, 'tmp', request.params.slug + '.zip'));
			return;
		}
	});

	resolve.send('stuff');
});
