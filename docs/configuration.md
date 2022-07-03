# Configuration

See `.env` for the available configuration options. See `.env.prod.local.dist` for env options required to set for production.

## Mandatory options

- `APP_ENV`: controls the environment. Is set to `dev` for dev and `prod` for prod.
- `APP_SECRET`: is Symfony secret. Needs to be specifically set to randomly generated string for production!
- `MAILER_SENDER`: controls the sender e-mail address. Format: `'Sherlock Holmes <sherlock@example.com>'`
- `DATABASE_URL`: controls the full url to the database. Default can be used for dev, _must_ be set for production.

### Azure Ad Single Sign on:

Register an application on https://portal.azure.com/#blade/Microsoft_AAD_IAM/ActiveDirectoryMenuBlade/RegisteredApps.

Add `Web` platform and specify redirect url: `https://<domain>(:<port>)/single-sign-on/azure-ad/callback/`.

For `Front-channel logout URL` set url to: `https://<domain>(:<port>)/sign-out`.

Add a secret, and fill in the `.env` options below:

- `OAUTH_AZURE_AD_TENANT_ID`: the `Directory (tenant) ID` from azure ad
- `OAUTH_AZURE_AD_CLIENT_ID`: the `Application (client) ID` from azure ad
- `OAUTH_AZURE_AD_CLIENT_SECRET`: the secret `value` created in the step above.

## Extra options

- `NGINX_VERSION`: controls the version of nginx.
- `NGINX_PORT`: controls the port nginx is started on. Default 8080 for dev, 80 for prod.
- `NGINX_SSL_PORT`: controls the https port for nginx. Default 8443 for dev, 443 for prod.
- `PHP_VERSION`: controls the php version. Defaults to 8.1
- `MYSQL_VERSION`: controls the mysql versions. Default to 8.0
- `MYSQL_PORT`: controls the mysql port. Default to 3306
- `MAILER_DSN`: controls the used mail procotol, default to the docker image: `smtp://mail:25`.
- `HTTP_CLIENT_VERIFY_HOST`: controls if curls VERIFY_HOST is enabled for API calls. `true` recommended for production.
- `HTTP_CLIENT_VERIFY_PEER`: controls if curls VERIFY_PEER is enabled for API calls. `true` recommended for production.
- `ERROR_MAIL`: specify the email-address error mails should be send to.
