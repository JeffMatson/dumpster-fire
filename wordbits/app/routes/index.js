const app = module.exports = require('express')();

app.use('/cache', require('./cache'));

app.all('*', function (req, res) {
	res.status(404).send('You broke something.');
});
