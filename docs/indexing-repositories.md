# Indexing repositories

Once repositories have been added, the `FetchRevisionsCommand` should be executed to fetch the revisions from each repositories and create
the appropriate reviews for them. If the project is started in `production`-mode this command will be automatically scheduled via the crontab.
For `development`-mode you can trigger this in two ways.

## Console

Run the cli command manually:
```shell
docker exec -ti 123view-php php bin/console revisions:fetch -vv
```
This will fetch all revisions since the last time the revisions were fetched and create reviews for them.

## Web
To manually trigger fetching new revisions from the frontend you can call the following endpoint:
```text
https://<domain>:<port/~vcs/<repository-id-or-name>
```
This will dispatch a fetch revisions message to the message bus and new revisions will be fetched in the background. This endpoint
can also be used by ci pipelines to immediately notify the project to fetch new revisions.

## Sync revisions
The `revisions:fetch` command will only index newly incoming revisions. To sync your locally stored revisions with the remote
and adding/removing any missing/removed revisions run the following command:

```shell
docker exec -ti 123view-php php bin/console revisions:validate -vv
```
This will compare all remote hashes and local hashes and added any new, and cleanup any absent.
