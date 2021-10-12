[![Minimum PHP Version](https://img.shields.io/badge/php-%3E%3D%207.4-8892BF)](https://php.net/)
[![Symfony Version](https://img.shields.io/badge/symfony-5.3-4BC51D)](https://symfony.com/releases)
[![PHPStan](https://img.shields.io/badge/phpstan-enabled-4BC51D)](https://www.phpstan.com/)
[![Coverage](https://img.shields.io/badge/coverage-100%25-4BC51D)](https://php.net/)
[![Build Status](https://github.com/123inkt/git-commit-notification/workflows/Check/badge.svg?branch=master)](https://github.com/123inkt/git-commit-notification/actions)
[![Build Status](https://github.com/123inkt/git-commit-notification/workflows/Test/badge.svg?branch=master)](https://github.com/123inkt/git-commit-notification/actions)

# Git commit notification
A symfony application to allow receiving commit notification for all commits in a certain time period.

**Features:**
- Receive one mail for all commits within a certain time period. Once per one, two, three, fours hours or daily or weekly.
- Exclude (or include) certain commit messages, files, or authors.
- Receive commits in a single mail for multiple repositories.
- Light or dark theme notification mail.
- Add links to your task or jira board based on the commit message.

**Examples:**
- Watch changes for `composer.json` for one or more repositories.
- Exclude all commits done by ci- or other automated processes.
- Exclude changes done to `composer.lock` for a repository.

**Themes**

<img src="docs/images/upsource.png" alt="Upsource" title="Upsource" width="400">
<img src="docs/images/darcula.png" alt="Darcula" title="Darcula" width="400">

## Requirements

- recent version of `git`
- php version > 7.4

## Quick start

```shell
git clone https://github.com/123inkt/git-commit-notification.git git-commit-notification
cd git-commit-notification
composer install --optimize-autoloader --classmap-authoritative --no-dev --no-progress
composer dump-env prod
```
Check `.env` for mailer settings, update (if necessary)
```dotenv
MAILER_DSN=native://default
MAILER_SENDER='Sherlock Holmes <sherlock@example.com>'
```

Create config in the root of the project:

```xml
<?xml version="1.0" encoding="UTF-8" ?>
<configuration xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:noNamespaceSchemaLocation="config.xsd">
    <repositories>
        <repository name="example" url="https://username:password@github.com/example.git"/>
    </repositories>

    <rule>
        <name>Example repository once per hour</name>
        <repositories>
            <repository name="example"/>
        </repositories>
        <recipients>
            <recipient email="sherlock@example.com" name="Sherlock Holmes"/>
        </recipients>
    </rule>
</configuration>
```
See [configuration](docs/configuration.md) for more configuration options.

### Add to crontab:

```shell
0 */1 * * *   /usr/bin/php /path/to/bin/console mail --frequency=once-per-hour         > /dev/null 2>&1
0 */2 * * *   /usr/bin/php /path/to/bin/console mail --frequency=once-per-two-hours    > /dev/null 2>&1
0 */3 * * *   /usr/bin/php /path/to/bin/console mail --frequency=once-per-three-hours  > /dev/null 2>&1
0 */4 * * *   /usr/bin/php /path/to/bin/console mail --frequency=once-per-four-hours   > /dev/null 2>&1
0 0 * * *     /usr/bin/php /path/to/bin/console mail --frequency=once-per-day          > /dev/null 2>&1
0 0 * * 1     /usr/bin/php /path/to/bin/console mail --frequency=once-per-week         > /dev/null 2>&1
```

See [command line options](docs/command-line.md) for more information about the console commands.

## Under the hood

1) Will fetch all commits for a given repository via the `git log` command.
2) Will bundle commits when author, branch and subject are identical.
3) For a set of commits, fetches the bundled changes between the first commit and the last
4) Send a notification mail in the desired formatting

## Troubleshooting

I'm not getting mails:
- In `.env` verify MAILER_DSN is set correctly. See https://symfony.com/doc/current/mailer.html#using-built-in-transports
- Run command with `-vvv` for verbose output. Verify your rules are configured correctly.
- send a test mail `php bin/console test:mail sherlock@example.com`
- Check the mail log for additional error messages. Depending on your system check:
  - /var/log/maillog
  - /var/log/mail.log
  - /var/adm/maillog
  - /var/adm/syslog/mail.log

## About us

At 123inkt (Part of Digital Revolution B.V.), every day more than 30 developers are working on improving our internal ERP and our several shops. Do
you want to join us? [We are looking for developers](https://www.werkenbij123inkt.nl/vacatures).
