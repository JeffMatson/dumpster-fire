const app = module.exports = require('express')();
const routes = require('app/routes');

app.use(routes);

app.listen(process.env.PORT || 3000, () => console.log('Running downloader API'));
