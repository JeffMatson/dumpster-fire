module.exports = function () {
	switch (process.env.NODE_ENV) {
		case 'development':
			process.env.NODE_TLS_REJECT_UNAUTHORIZED = "0";
			return {
				siteDomain: 'wordbits.localhost',
				port: 3000,
				useSSL: true,
			};
		case 'production':
			return {
				siteDomain: 'wordbits.io',
				port: process.env.PORT,
				useSSL: true,
				api_keys: [
					'CHANGEME'
				]
			};
		default:
			throw new Error('Environment not defined!');
	}
};
