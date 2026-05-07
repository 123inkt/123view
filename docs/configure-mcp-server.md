# Configure 123view mcp server

## Copilot

1) Create access token at `/app/user/access-tokens`
1) Type: `http`
1) URL: `https://123view.my-host.nl/_mcp`
1) Headers: `{"Authorization": "Bearer 1234567890abcdefghijklmnopqrstuvwxyz"}`
1) Tools: `*`

### Manual configuration

Edit `~/.copilot/mcp-config.json`
```json
{
    "mcpServers": {
        "123view": {
            "type": "http",
            "url": "https://123view.my-host.nl/_mcp",
            "headers": {
                "Authorization": "Bearer 1234567890abcdefghijklmnopqrstuvwxyz"
            }
        }
    }
}
```

## Available tools
- `add_comment`: Add a comment to a code review at a specific file and line number. Optionally include a code suggestion.
- `get_code_review_comments`: Get all comments on the given review. Returns a list of comments with their id, author, content, file path, line number and optionally a code suggestion.
- `get_code_review_diff`: Get the diff for all the changes done in the review. Returns a list of files with their path, name and diff.
- `get_code_review`: Find the first code review matching the given filters. All provided filters are applied as AND conditions. Returns null when no match is found.
- `get_code_reviews`: Search for code reviews using optional filters. All provided filters are applied as AND conditions. Returns up to 50 results ordered by most recently updated.
- `get_current_user`: Returns the id, name and email of the currently authenticated user.
- `list_files`: List the files in the given directory path for the specified code review.
- `read_file`: Reads the contents of a file for the given path and review. Returns the file contents as a string. Only searches in the git repository of the specified code review and will not find any dependencies.
