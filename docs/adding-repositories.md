# Adding repositories

Once docker has started, your project is available, but no repositories have been added yet. Follow the steps below to add a repository:

1) Start bash in your docker container:
```shell
docker exec -ti php-fpm /bin/bash
```
2) Add a repository
```shell
php bin/console git:repository:add https://<user>:<password>gitlab.com/name/of/your/repository --name="my-repo"
```

## Adding upsource and/or gitlab integration

```shell
php bin/console git:repository:add https://<user>:<password>@gitlab.com/name/of/your/repository --name="my-repo" --gitlab=196 --upsource="myrepo"
```
`--gitlab` should receive the gitlab project id of this repository, and a link to the merge request will be added.

`--upsource` should receive the upsource project name of for this repository, and a link to the upsource review will be added.

## List available repositories

```shell
php bin/console git:repository:list
```

## Remove a repository

```shell
php bin/console git:repository:remove 'my-repo'
```
