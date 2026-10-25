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

- `APP_AUTH_AZURE_AD`: set to true to enable azure ad sso.
- `OAUTH_AZURE_AD_TENANT_ID`: the `Directory (tenant) ID` from azure ad
- `OAUTH_AZURE_AD_CLIENT_ID`: the `Application (client) ID` from azure ad
- `OAUTH_AZURE_AD_CLIENT_SECRET`: the secret `value` created in the step above.

### Code review grouping pattern
When revisions are gathered from a repository, they will be assigned to a review that matches the `CODE_REVIEW_MATCHING_PATTERN`. In certain
situations, you have multiple matching groups within a single pattern, and you can specify which groups should be used as match.

- `CODE_REVIEW_MATCHING_PATTERN`
- `CODE_REVIEW_MATCHING_GROUPS`

Example:
```
CODE_REVIEW_MATCHING_PATTERN='^(?:US#\d+\s(?<storyBugOrTask>[BT]#\d+))|(?<bug>B#\d+)'
CODE_REVIEW_MATCHING_GROUPS='storyBugOrTask,bug'

Commit message: US#456 T#123 my first task
> matches: 'T#123', group 'storyBugOrTask'

Commit message: B#789 my first bug
> matches: 'B#789', group 'bug'
```

## Custom development TLS certificates

To use an already generated certificate (for example, one issued by your internal CA), set both paths in `.env.dev.local`:

```dotenv
SSL_CERTIFICATE=/etc/letsencrypt/live/phoebe.drshop.123dev.nl/fullchain.pem
SSL_CERTIFICATE_KEY=/etc/letsencrypt/live/phoebe.drshop.123dev.nl/privkey.pem
```

These are paths on the Docker host. On Windows, use forward slashes, for example `C:/certificates/fullchain.pem`.
The certificate file should include the server certificate and any intermediate certificates, and the key must match it.
The certificate must include the actual hostname in its Subject Alternative Names: `*.123dev.nl` does not cover
`phoebe.drshop.123dev.nl`. The issuing root CA must also be trusted by your browser/operating system.

Apply the settings with `./bin/start.sh --dev --skip-log`. Both nginx and Mercure mount the files read-only.
When either variable is unset or empty, that file defaults to the existing certificate or key in `docker/ssl/development/`.
Set both variables together to avoid a mismatched certificate/key pair.

When running Docker Compose directly, explicitly load the local environment file:

```bash
docker compose --env-file .env --env-file .env.dev.local up -d nginx my_mercure
```

Certificate issuance and renewal are managed outside the application. After renewal, recreate the containers to load the updated files:

```bash
docker compose --env-file .env --env-file .env.dev.local up -d --force-recreate nginx my_mercure
```

## Extra options

- `NGINX_VERSION`: controls the version of nginx.
- `NGINX_ANGULAR_PORT`: controls the https port for nginx. Default 8443 for dev, 443 for prod.
- `NGINX_SYMFONY_PORT`: controls the https port for symfony+nginx. Default 9443 for dev and prod.
- `PHP_VERSION`: controls the php version. Defaults to 8.4
- `MYSQL_VERSION`: controls the mysql versions. Default to 8.0
- `MYSQL_PORT`: controls the mysql port. Default to 3306
- `MAILER_DSN`: controls the used mail procotol, default to the docker image: `smtp://mail:25`.
- `HTTP_CLIENT_VERIFY_HOST`: controls if curls VERIFY_HOST is enabled for API calls. `true` recommended for production.
- `HTTP_CLIENT_VERIFY_PEER`: controls if curls VERIFY_PEER is enabled for API calls. `true` recommended for production.
- `ERROR_MAIL`: specify the email-address error mails should be send to.
