# Command line

## Send a mail notification

```shell
php bin/console mail --frequency=once-per-hour --config=<path-to-config>
```
| Options | Required | Values | Description |
|---------|----------|--------|-------------|
| `--frequency` | yes | once-per-hour, once-per-hour<br>once-per-two-hours<br>once-per-three-hours<br>once-per-four-hours<br>once-per-day<br>once-per-week | The frequency of the rules that should be executed. This argument should match the settings in the crontab. |
| `--config` | no | path-to-config | The path to the config `xml`, if absent will search for `config.xml` in the root of the project. |

## Send a test mail

To test the mail settings are configured correctly a test mail can be sent.

```shell
php bin/console test:mail sherlock@example.com
```

