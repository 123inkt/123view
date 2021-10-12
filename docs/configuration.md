# Configuration

Create the following basic `config.xml`:

```xml
<?xml version="1.0" encoding="UTF-8"?>
<configuration xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
               xsi:noNamespaceSchemaLocation="config.xsd">
</configuration>
```

Add a rule for a repository you want to receive notifications for:

```xml
<?xml version="1.0" encoding="UTF-8"?>
<configuration xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
               xsi:noNamespaceSchemaLocation="config.xsd">
    <repositories>
        <repository name="my-repo" url="https://username:password@git.example.com/repository/example.git"/>
    </repositories>

    <rule>
        <name>MyFirstNotification</name>
        <repositories>
            <repository name="my-repo"/>
        </repositories>
        <recipients>
            <recipient email="sherlock@example.com" name="Sherlock Holmes"/>
        </recipients>
    </rule>
</configuration>
```

**Requirements:**

- A name of the rule, this will be added to the subject of the notification.
- One or more repositories to scan. Add credentials to the url.
- One or more repository references in the rule.
- One or more recipients.

## Rule options

| Option | Default | Values | Description |
|--------|---------|--------|-------------|
| active | true | true,false | toggle to enable/disable a rule |
| theme | upsource | upsource,darcula | either light or dark theme in the mail |
| frequency | once-per-hour | once-per-hour<br>once-per-two-hours<br>once-per-three-hours<br>once-per-four-hours<br>once-per-day<br>once-per-week | the frequency the notification will be sent for this rule | 
| diffAlgorithm | histogram | myers,patience,minimal,histogram  | diff algorithm type, see: https://git-scm.com/docs/git-diff |
| excludeMergeCommits | true | true,false | should merge commits be excluded? |
| ignoreAllSpace | false | true,false | should _all_ whitespace changes be ignored? |
| ignoreBlankLines | false | true,false | should blank lines be ignored? |
| ignoreSpaceAtEol | true | true,false | should whitespace at the end of the line be ignored?|
| ignoreSpaceChange | false | true,false | should whitespace changes be ignored? |

```xml
<?xml version="1.0" encoding="UTF-8"?>
<configuration xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
               xsi:noNamespaceSchemaLocation="config.xsd">
    <rule active="true"
          theme="darcula"
          frequency="once-per-week"
          diffAlgorithm="myers"
          excludeMergeCommits="true"
          ignoreAllSpace="false"
          ignoreBlankLines="true"
          ignoreSpaceAtEol="true"
          ignoreSpaceChange="true">
        ...
    </rule>
</configuration>
```

## Inclusion and Exclusions

It's possible to exclude or include entire commits by author or subject, aswell as excluding/including certain files or directories within commits.
For file and subject inclusion/exclusion it expects regex pattern accepted by `preg_match`.

### Example

- Exclude all commits where the commit message starts with `JB1234`.
- Exclude `composer.lock` from all commits.
- Only include commits from `sherlock@example.com` and `watson@example.com`.

```xml
<?xml version="1.0" encoding="UTF-8"?>
<configuration xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
               xsi:noNamespaceSchemaLocation="config.xsd">
    <rule>
        ...
        <exclude>
            <subject>#^JB1234#</subject>
            <file>#^composer\.lock$#</file>
        </exclude>
        <include>
            <author>sherlock@example.com</author>
            <author>watson@example.com</author>
        </include>
    </rule>
</configuration>
```

## Add external links

With certain commits the ticket or task number is included in the commit message. With the external links options these can be converted to links. Add
the `external_links` section to the rule:

```xml
<?xml version="1.0" encoding="UTF-8"?>
<configuration xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
               xsi:noNamespaceSchemaLocation="config.xsd">
    <rule>
        ...
        <external_links>
            <external_link pattern="T#{}" url="https://jira.example.com/entity/{}"/>
            <external_link pattern="B#{}" url="https://jira.example.com/entity/{}"/>
        </external_links>
    </rule>
</configuration>
```

## Upsource review link integration

To add an icon + url to the review of each commit, you have to specify the upsource project id for your repository:

```xml
<repositories>
    <repository name="my-repo"
                url="https://username:password@git.example.com/repository/example.git"
                upsource-project-id="my-project"
    />
</repositories>
```

And in `.env` configure `UPSOURCE_API_URL` and `UPSOURCE_BASIC_AUTH`. See the `.env` for configurations examples.

## Gitlab merge request or branch link integration

To add an icon + url to the merge request or branch for each commit, you have to specify the gitlab project id for your repository:

```xml
<repositories>
    <repository name="my-repo"
                url="https://username:password@git.example.com/repository/example.git"
                gitlab-project-id="165"
    />
</repositories>
```

And in `.env` configure `GITLAB_API_URL` and `GITLAB_ACCESS_TOKEN`. See the `.env` for configurations examples.
