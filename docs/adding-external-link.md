# Adding externals links

When a commit message contains an id to your task board like jira, a pattern + url which will translate a
commit message to clickable links to your task board.

For example:

`T#{}` and `http://jira.atlassian.com/task/{}` will transform `T#12345` to and url to `http://jira.atlassian.com/task/12345`.

## Add a pattern and url

In your php-fpm docker container:
```shell
php bin/console external-link:add 'T#{}' 'http://jira.atlassian.com/task/{}'
```

## List configured external links
```shell
php bin/console external-link:list
```

## Remove an external link
Use the list command to see the external link ids, and pass the id as argument to the following command

```shell
php bin/console external-link:remove <id>
```
